# Entity Schema and DDL

LowlyPHP will auto-generate storage schema using a data definition language (DDL).

In practical terms, this means a developer can build a data model without writing any DBMS-specific code to support it.
Consider the following data model:

- Customer
  - ID
  - First Name
  - Last Name
  - Email Address

This `customer` _entity_ uses three _attributes_. In modeling terms, you would generally use some form of "getter" and
"setter" to manage their values. But you would also have to create a way to store this information. Now consider that a
future update to this entity introduces a fourth attribute:

- Tax ID

If you have already defined a structure for the original entity, this new attribute may require an update to the
original structure.

Entity Schema solves this problem by managing storage structures automatically. The default MySQL driver implementation
will employ various services to convert your entity into MySQL table definitions. 

That is, our initial version of the entity would be converted to something similar to the following SQL:

    CREATE TABLE `customer` (
        `entity_id` INT(10) AUTO_INCREMENT PRIMARY KEY,
        `first_name` TEXT,
        `last_name` TEXT,
        `email_address` TEXT
    )

With this structure already applied, the update to our entity would result in a new SQL update:

    ALTER TABLE `customer` ADD COLUMN `tax_id` TEXT

The application will manage these updates for you by comparing your entity's class structure to its DBMS structure.

## Limitations

Auto-generated schema has some limitations:

 1. Optimized structures are not guaranteed
 2. Drivers are responsible for supporting all DBMS features and may lack coverage
 3. Structures are tied directly to entities, which limits schema customizations
 4. The default MySQL driver does not fully support many types of changes

This is one reason why, in the sample MySQL shown above, `TEXT` columns are shown where traditionally a `VARCHAR` column
would be used. While there is some debate about the performance advantages of one data type over the other, the fact is
that each storage driver chooses the most appropriate type for you. The trade-off to this approach is in time saved and
and adequate performance.

## DDL Features

You've learned about how schema is generated for you. You've also understood the limitations to this approach. Yet DDL
still provides a way to control the schema in a driver-agnostic way.

There are 5 components of the DDL, represented by service contracts:

 * `SchemaInterface` – The entity structure as a PHP class
 * `ColumnInterface` – A subclass of schema, a column, which represents an entity attribute as a PHP class
 * `SchemaMapperInterface` – The service which converts an entity data structure to a DDL structure
 * `SchemaStorageInterface` – A driver service trait which defines the DDL properties
 * `ConverterInterface` – A driver service which translates schema into DBMS-specific commands

These components enable an extension developer to better control the storage behavior when automation is insufficient.

## Example: SQL Foreign Keys

A simple entity, like the customer example described earlier, has no complex relationship to other data. If it did, we
may want to enforce that relationship in our storage medium. Using the default MySQL driver, it is possible to define
a foreign key constraint using the DDL prescribed by the `SchemaStorageInterface` service.

To define a foreign key, you must implement a custom schema for your entity. This process takes 3 steps:

 1. Create the entity(ies) from `EntityInterface`
 2. Create the schema from `SchemaInterface`
 3. Add these to configuration

### Create the Entity

A foreign key constraint in SQL typically requires at least 2 tables. For this example, let's assume that the
relationship will be between the `ProductInterface` and our new entity. Our entity will be called "upsell," and it will
manage links to other related, promoted products. This new entity will be defined as follows:

    namespace LowlyPHP\Provider\Catalog\Product;
    
    use LowlyPHP\Service\Resource\EntityInterface;

    class Upsell implements EntityInterface
    {
        const TARGET_ID = 'target_id';
        const PRODUCT_ID = 'product_id';
    
        private $data = [
            self::ID => 0,
            self::TARGET_ID => 0,
            self::PRODUCT_ID => 0,
        ];
    
        public function export() : array
        {
            return [
                self::ID => $this->getEntityId(),
                self::TARGET_ID => $this->getTargetId(),
                self::PRODUCT_ID => $this->getProductId(),
            ];
        }
    
        public function getEntityId() : int
        {
            return (int) $this->data[self::ID];
        }
    
        public function setEntityId(int $id) : void
        {
            $this->data[self::ID] = $id;
        }
    
        public function getTargetId() : int
        {
            return (int) $this->data[self::TARGET_ID];
        }
    
        public function setTargetId(int $id) : void
        {
            $this->data[self::TARGET_ID] = $id;
        }
    
        public function getProductId() : int
        {
            return (int) $this->data[self::PRODUCT_ID];
        }
    
        public function setProductId(int $id) : void
        {
            $this->data[self::PRODUCT_ID] = $id;
        }
    }

As long as your model implements `EntityInterface`, it will be compatible for schema management. In this state, our new
`Upsell` model will work out-of-the-box with no further effort. We can acquire and instance of this model, set its
properties, and use an `EntityManagerInterface` to commit the data to storage.

### Create the Schema

While our data model is ready for use, it is not well prepared for changes to the product table. If a product record is
removed, any references to it in our `Upsell` records will be left behind. To keep things clean, we would want to setup
a relationship to the product table, so that deleted products will trigger deleted upsell associations.

To do this, we can create a custom implementation of `SchemaInterface`, as shown below:

    namespace LowlyPHP\Provider\Catalog\Product\Upsell;
    
    use LowlyPHP\Provider\Catalog\Product\Upsell;
    use LowlyPHP\Provider\Resource\Storage\Schema as BaseSchema;
    use LowlyPHP\Provider\Resource\Storage\Schema\ColumnFactory;
    use LowlyPHP\Service\Resource\Storage\SchemaStorageInterface;
    
    class Schema extends BaseSchema
    {
        public function __construct(
            string $name,
            string $source,
            array $columns,
            ColumnFactory $columnFactory
        ) {
            foreach ($columns as $index => $column) {
                /** @var array $metadata */
                $metadata = $column->getMetadata();
                $update = false;
    
                switch ($column->getName()) {
                    case Upsell::TARGET_ID :
                        $metadata[SchemaStorageInterface::META_KEY_RELATIONSHIPS] = [
                            [ProductInterface::class, ProductInterface::ID]
                        ];
                        $metadata[SchemaStorageInterface::META_KEY_INDEXES] = [
                            ['unique' => true]
                        ];
                        $update = true;
                        break;
                    case Upsell::PRODUCT_ID :
                        $metadata[SchemaStorageInterface::META_KEY_INDEXES] = [
                            ['unique' => true]
                        ];
                        $update = true;
                        break;
                    default:
                        break;
                }
    
                if ($update) {
                    $columns[$index] = $columnFactory->create(
                        $column->getName(),
                        $column->getLength(),
                        $column->getType(),
                        $metadata
                    );
                }
            }
    
            parent::__construct($name, $source, $columns);
        }
    }

This schema definition is actually an extension of the generic schema provider. Because the generic provider will
automatically prepare our schema definition by analyzing our `Upsell` model, we need only to enhance the given columns
with our relationship DDL properties. The properties used here are:

- META_KEY_RELATIONSHIPS
- META_KEY_INDEXES

For more information about DDL properties, see `SchemaStorageInterface`.

### Add These to Configuration

With our entity and schema defined, let's bind them together via configuration:

    {
        "providers": {
            "LowlyPHP\\Provider\\Catalog\\Product\\Upsell": {
                "schema": {
                    "name": "default",
                    "source": "catalog_product_link_upsell",
                    "class": "LowlyPHP\\Provider\\Catalog\\Product\\Upsell\\Schema"
                }
            }
        }
    }

It should be noted that the `name` and `source` properties are automatically passed into the upsell schema instance.
While it is technically possible to omit these from configuration and instead define them within your custom schema,
by placing them into configuration you give greater control over the application outside of the source code.
