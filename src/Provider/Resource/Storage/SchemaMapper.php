<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Resource\Storage;

use LowlyPHP\ApplicationManager;
use LowlyPHP\Exception\ConfigException;
use LowlyPHP\Provider\Resource\Storage\Schema\ColumnFactory;
use LowlyPHP\Service\Resource\SerializableInterface;
use LowlyPHP\Service\Resource\EntityInterface;
use LowlyPHP\Service\Resource\ScopedEntityInterface;
use LowlyPHP\Service\Resource\Storage\Schema\Column\ConditionProcessorInterface;
use LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface;
use LowlyPHP\Service\Resource\Storage\SchemaInterface;
use LowlyPHP\Service\Resource\Storage\SchemaMapperInterface;
use LowlyPHP\Service\Resource\Storage\SchemaStorageInterface;

/**
 * Schema mapper implementation for {@see SchemaMapperInterface}.
 *
 * The default mapping behavior relies on {@see EntityInterface::export} to convert properties of an entity to schema.
 */
class SchemaMapper implements SchemaMapperInterface
{
    const DEFAULT_NAME = 'default';
    const DEFAULT_LENGTH = '0';

    /** @var ApplicationManager */
    private $app;

    /** @var SchemaInterface[] */
    private $cache;

    /** @var ColumnFactory */
    private $columnFactory;

    /** @var array */
    private $defaultLengths = [
        ColumnInterface::TYPE_STRING => self::DEFAULT_LENGTH,
        ColumnInterface::TYPE_COMPOUND => self::DEFAULT_LENGTH,
        ColumnInterface::TYPE_BOOL => '1',
        ColumnInterface::TYPE_SERIALIZED => self::DEFAULT_LENGTH,
        ColumnInterface::TYPE_FLOAT => '12,4',
        ColumnInterface::TYPE_INT => '12',
    ];

    /** @var SchemaFactory */
    private $schemaFactory;

    /**
     * @param SchemaFactory $schemaFactory
     * @param ColumnFactory $columnFactory
     * @param ApplicationManager|null $app
     * @codeCoverageIgnore
     */
    public function __construct(
        SchemaFactory $schemaFactory,
        ColumnFactory $columnFactory,
        ApplicationManager $app = null
    ) {
        $this->app = $app ?? ApplicationManager::getInstance();
        $this->schemaFactory = $schemaFactory;
        $this->columnFactory = $columnFactory;
        $this->cache = [];
    }

    /**
     * {@inheritdoc}
     */
    public function map(EntityInterface $entity) : SchemaInterface
    {
        $id = \sha1(\get_class($entity));

        if (empty($this->cache[$id])) {
            $this->cache[$id] = $this->getSchema($entity);
        }

        return $this->cache[$id];
    }

    /**
     * Generate schema dynamically from the given parameters.
     *
     * @param EntityInterface $entity
     * @param array $config
     * @return SchemaInterface
     * @throws ConfigException
     * @throws \ReflectionException
     */
    private function createSchema(EntityInterface $entity, array $config) : SchemaInterface
    {
        if (empty($config) || !isset($config['name'], $config['source'])) {
            throw new ConfigException(
                sprintf('Invalid schema configuration for %s.', \get_class($entity))
            );
        }

        return $this->schemaFactory->create(
            $config['name'],
            $this->getSourceName($entity, $config['source'] ?? ''),
            $this->getColumns($entity)
        );
    }

    /**
     * Attempt to convert a complex return type to its corresponding column type.
     *
     * @param string $type
     * @return string
     * @throws \ReflectionException
     */
    private function extractColumnTypeFromComplexType(string $type) : string
    {
        if (\class_exists($type)) {
            if ($type === SerializableInterface::class) {
                return ColumnInterface::TYPE_SERIALIZED;
            }

            /** @var \ReflectionClass $descriptor */
            $descriptor = new \ReflectionClass($type);

            if ($descriptor->hasMethod('__toString')) {
                return ColumnInterface::TYPE_STRING;
            }
        }

        return '';
    }

    /**
     * Convert a given method return type to its corresponding column type.
     *
     * @param string $type
     * @return string
     * @throws \ReflectionException
     */
    private function extractType(string $type) : string
    {
        switch (\strtolower($type)) {
            case 'void' :
                return '';
            case 'null' :
            case 'integer' :
                return ColumnInterface::TYPE_INT;
            case 'double' :
                return ColumnInterface::TYPE_FLOAT;
            case 'array' :
                return ColumnInterface::TYPE_SERIALIZED;
            case 'boolean' :
                return ColumnInterface::TYPE_BOOL;
            case 'string' :
                return ColumnInterface::TYPE_STRING;
            default :
                return $this->extractColumnTypeFromComplexType($type);
        }
    }

    /**
     * Extract column definitions from the given entity.
     *
     * @param EntityInterface $entity
     * @return \LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface[]
     * @throws \ReflectionException
     * @throws ConfigException
     */
    private function getColumns(EntityInterface $entity) : array
    {
        /** @var array $export */
        $export = $entity->export();
        /** @var array $columns */
        $columns = [];

        /** @var \ReflectionMethod $method */
        foreach ($export as $key => $value) {
            /** @var string $type */
            $type = $this->extractType(
                \is_object($value) ? \get_class($value) : \gettype($value)
            );

            if (empty($type)) {
                continue;
            }

            $columns[] = $this->columnFactory->create(
                $key,
                $this->defaultLengths[$type] ?? static::DEFAULT_LENGTH,
                $type,
                [
                    SchemaStorageInterface::META_KEY_IDENTIFIER => $key === EntityInterface::ID,
                    SchemaStorageInterface::META_KEY_CONDITION_PROCESSOR => $this->getConditionProcessor(
                        \get_class($entity),
                        $key
                    )
                ]
            );
        }

        return $columns;
    }

    /**
     * @param string $entityClass
     * @param string $field
     * @return ConditionProcessorInterface|null
     * @throws ConfigException
     */
    private function getConditionProcessor(string $entityClass, string $field) : ?ConditionProcessorInterface
    {
        try {
            $class = $this->app->config(
                \sprintf('providers.%s.schema.columns.%s.condition', $entityClass, $field)
            );

            if (empty($class) || !\class_exists($class)) {
                return null;
            }

            $processor = $this->app->getObject($class);

            if (!($processor instanceof ConditionProcessorInterface)) {
                throw new ConfigException(
                    \sprintf(
                        'Condition processor for field "%s" must be an instance of ConditionProcessorInterface.',
                        $field
                    )
                );
            }

            return $processor;
        } catch (ConfigException $error) {
            throw $error;
        } catch (\Exception $error) {
            return null;
        }
    }

    /**
     * Lookup defined schema configuration for the given class..
     *
     * @param string $class
     * @return array
     * @throws ConfigException
     */
    private function getConfig(string $class) : array
    {
        return (array) $this->app->config(
            sprintf('providers.%s.schema', $class)
        );
    }

    /**
     * Generate schema from the given entity.
     *
     * @param EntityInterface $entity
     * @return SchemaInterface
     * @throws ConfigException
     * @throws \ReflectionException
     */
    private function getSchema(EntityInterface $entity) : SchemaInterface
    {
        /** @var string[] $classes */
        $classes = \array_merge([\get_class($entity)], (array) \class_implements($entity));
        /** @var array $config */
        $config = [];

        /** @var string $class */
        foreach ($classes as $class) {
            $config = \array_filter($this->getConfig($class));

            if (!empty($config)) {
                break;
            }
        }

        if (!empty($config['class'])) {
            /** @var SchemaInterface $schema */
            $schema = $this->app->createObject(
                $config['class'],
                [
                    'name' => $config['name'] ?? '',
                    'source' => $this->getSourceName($entity, $config['source'] ?? ''),
                    'columns' => $this->getColumns($entity),
                ]
            );

            if (!($schema instanceof SchemaInterface)) {
                throw new ConfigException(
                    \sprintf('%s must be an instance of %s', $config['class'], SchemaInterface::class)
                );
            }

            return $schema;
        }

        return $this->createSchema($entity, $config);
    }

    /**
     * Resolve the storage source name from the given data.
     *
     * Used to handle source name transformations, including scoped storage mapping.
     *
     * @param EntityInterface $entity
     * @param string $sourceName
     * @return string
     */
    private function getSourceName(EntityInterface $entity, $sourceName = '') : string
    {
        if (empty($sourceName)) {
            return '';
        }

        if ($entity instanceof ScopedEntityInterface && $entity->getScopeId() > 0) {
            return \sprintf('%s_%d', $sourceName, $entity->getScopeId());
        }

        return $sourceName;
    }
}
