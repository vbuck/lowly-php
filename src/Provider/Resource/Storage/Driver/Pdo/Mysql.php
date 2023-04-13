<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Resource\Storage\Driver\Pdo;

use LowlyPHP\ApplicationManager;
use LowlyPHP\Exception\StorageReadException;
use LowlyPHP\Exception\StorageWriteException;
use LowlyPHP\Provider\Resource\Storage\Schema\ColumnFactory;
use LowlyPHP\Provider\Resource\Storage\Schema\Converter\Sql as SchemaConverter;
use LowlyPHP\Provider\Resource\Storage\SchemaFactory;
use LowlyPHP\Service\Resource\EntityInterface;
use LowlyPHP\Service\Resource\SerializerInterface;
use LowlyPHP\Service\Resource\Storage\Schema\Column\ConditionProcessorPoolInterface;
use LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface;
use LowlyPHP\Service\Resource\Storage\SchemaInterface;
use LowlyPHP\Service\Resource\Storage\SchemaStorageInterface;
use LowlyPHP\Service\Resource\StorageInterface;

/**
 * PDO-MySQL storage implementation for {@see StorageInterface}.
 */
class Mysql implements StorageInterface, SchemaStorageInterface
{
    const CONFIG_NAME = 'name';
    const CONFIG_CHARSET = 'charset';
    const CONFIG_HOST = 'host';
    const CONFIG_PASS = 'pass';
    const CONFIG_PORT = 'port';
    const CONFIG_TABLE = 'table';
    const CONFIG_USER = 'user';

    /** @var array */
    protected $config = [
        self::CONFIG_NAME => '',
        self::CONFIG_CHARSET => 'utf8',
        self::CONFIG_HOST => 'localhost',
        self::CONFIG_PASS => '',
        self::CONFIG_PORT => '3306',
        self::CONFIG_TABLE => '',
        self::CONFIG_USER => '',
    ];

    /** @var \PDO */
    protected $connection;

    /** @var string */
    protected $connectionName = 'default';

    /** @var string */
    protected $identifier = EntityInterface::ID;

    /** @var SchemaInterface */
    protected $schema;

    protected $typeMap = [
        'int' => ColumnInterface::TYPE_INT,
        'tinyint' => ColumnInterface::TYPE_BOOL,
        'decimal' => ColumnInterface::TYPE_FLOAT,
        'text' => ColumnInterface::TYPE_STRING,
        'varchar' => ColumnInterface::TYPE_STRING,
    ];

    /** @var ApplicationManager */
    private $app;

    /** @var ColumnFactory */
    private $columnFactory;

    /** @var ConditionProcessorPoolInterface */
    private $conditionProcessorPool;

    /** @var bool */
    private $isPrepared = false;

    /** @var SchemaConverter */
    private $schemaConverter;

    /** @var SchemaFactory */
    private $schemaFactory;

    /** @var SerializerInterface */
    private $serializer;

    /**
     * @param SchemaConverter $schemaConverter
     * @param SerializerInterface $serializer
     * @param SchemaFactory $schemaFactory
     * @param ColumnFactory $columnFactory
     * @param ConditionProcessorPoolInterface $conditionProcessorPool
     * @param SchemaInterface|null $schema
     * @param ApplicationManager|null $app
     * @throws StorageReadException
     * @throws \LowlyPHP\Exception\ConfigException
     */
    public function __construct(
        SchemaConverter $schemaConverter,
        SerializerInterface $serializer,
        SchemaFactory $schemaFactory,
        ColumnFactory $columnFactory,
        ConditionProcessorPoolInterface $conditionProcessorPool,
        SchemaInterface $schema = null,
        ApplicationManager $app = null
    ) {
        $this->app = $app ?? ApplicationManager::getInstance();
        $this->schemaConverter = $schemaConverter;
        $this->serializer = $serializer;
        $this->schemaFactory = $schemaFactory;
        $this->columnFactory = $columnFactory;
        $this->conditionProcessorPool = $conditionProcessorPool;
        $this->setConnection(false);

        if ($schema !== null) {
            $this->setSchema($schema);
        }
    }

    /**
     * Destroy connection.
     */
    public function __destruct()
    {
        $this->connection = null;
    }

    /**
     * @inheritdoc
     *
     * @param \LowlyPHP\Service\Api\FilterInterface[] $filters
     * @return int The total number of records matching the given criteria.
     * @throws \LowlyPHP\Exception\StorageReadException
     * @throws \InvalidArgumentException
     */
    public function count(array $filters) : int
    {
        try {
            $this->connect();
            $conditions = $this->prepareConditions($filters);
            /** @var \PDOStatement $statement */
            $statement = $this->connection->prepare(
                \trim(
                    \sprintf(
                        'SELECT COUNT(*) FROM `%s` %s %s',
                        $this->config[self::CONFIG_TABLE],
                        !empty($conditions) ? 'WHERE' : '',
                        \implode(' ', $conditions)
                    )
                )
            );
            $statement->execute();

            $result = $statement->fetchColumn(0);
            $statement = null;

            return $result;
        } catch (\PDOException $error) {
            throw new StorageReadException(\sprintf('Failed to query records: %s', $error->getMessage()));
        } catch (\Exception $error) {
            throw new StorageReadException(\sprintf('Encountered error of type: %s', \get_class($error)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id) : void
    {
        try {
            $this->connect();
            $statement = $this->connection->prepare(
                \sprintf(
                    'DELETE FROM `%s` WHERE `%s` = ?',
                    $this->config[self::CONFIG_TABLE],
                    $this->identifier
                )
            );
            $statement->execute([$id]);

            if (!$statement->rowCount()) {
                throw new StorageWriteException(sprintf('No record found with ID "%s"', $id));
            }

            $statement = null;
        } catch (\PDOException $error) {
            throw new StorageWriteException('Failed to delete record.');
        } catch (\Exception $error) {
            throw new StorageReadException(\sprintf('Encountered error of type: %s', \get_class($error)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(int $id) : array
    {
        try {
            $this->connect();
            /** @var \PDOStatement $statement */
            $statement = $this->connection->prepare(
                \sprintf(
                    'SELECT * FROM `%s` WHERE `%s` = ?',
                    $this->config[self::CONFIG_TABLE],
                    $this->identifier
                )
            );
            $statement->execute([$id]);

            $result = $statement->fetch(\PDO::FETCH_ASSOC) ?: [];
            $statement = null;

            return $result;
        } catch (\PDOException $error) {
            throw new StorageReadException(\sprintf('Failed to load record: %s', $error->getMessage()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function query(array $filters, int $page = 1, int $limit = 0) : array
    {
        try {
            $this->connect();
            $conditions = $this->prepareConditions($filters);
            $page = $page > 1 ? ($page - 1) : 0;

            /** @var \PDOStatement $statement */
            $statement = $this->connection->prepare(
                \trim(
                    \sprintf(
                        'SELECT * FROM `%s` %s %s%s',
                        $this->config[self::CONFIG_TABLE],
                        !empty($conditions) ? 'WHERE' : '',
                        \implode(' ', $conditions),
                        $limit > 0 ? \sprintf(' LIMIT %d, %d', $page, $limit) : ''
                    )
                )
            );
            $statement->execute();

            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $statement = null;

            return $result;
        } catch (\PDOException $error) {
            throw new StorageReadException(\sprintf('Failed to query records: %s', $error->getMessage()));
        } catch (\Exception $error) {
            throw new StorageReadException(\sprintf('Encountered error of type: %s', \get_class($error)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $data, int $id = null) : int
    {
        try {
            $this->connect();
            $columns = $values = [];

            if ($id !== null) {
                $data[$this->identifier] = $id;
            }

            foreach ($data as $column => $value) {
                $columns[] = '`' . str_replace('`', '\`', $column) . '`';
                $values[] = $value === null ? 'NULL' : $this->connection->quote((string) $value);
            }

            /** @var \PDOStatement $statement */
            $statement = $this->connection->prepare(
                \sprintf(
                    'INSERT INTO `%s` (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
                    $this->config[self::CONFIG_TABLE],
                    \implode(', ', $columns),
                    \implode(', ', $values),
                    \implode(
                        ', ',
                        \array_filter(
                            \array_map(
                                function ($column, $value) {
                                    if (\trim($column, '`') === $this->identifier) {
                                        return false;
                                    }

                                    return "{$column} = {$value}";
                                },
                                $columns,
                                $values
                            )
                        )
                    )
                )
            );

            $statement->execute();
            $statement = null;
            $lastInsertId = (int) ($this->connection->lastInsertId() ?: $data[$this->identifier]);

            return $lastInsertId;
        } catch (\PDOException $error) {
            throw new StorageWriteException(\sprintf('Failed to save record: %s', $error->getMessage()));
        } catch (\Exception $error) {
            throw new StorageWriteException(\sprintf('Encountered error of type: %s', \get_class($error)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setSchema(SchemaInterface $schema) : void
    {
        $this->schema = $schema;
        $this->connectionName = $schema->getName();
        $this->config[self::CONFIG_TABLE] = $schema->getSource();

        /** @var ColumnInterface $column */
        foreach ($schema->getColumns() as $column) {
            /** @var array $metadata */
            $metadata = $column->getMetadata();

            if (!empty($metadata[SchemaStorageInterface::META_KEY_IDENTIFIER])) {
                $this->identifier = $column->getName();
            }
        }

        $this->setConnection($this->connection === null);
    }

    /**
     * Open a connection to the data source.
     *
     * @throws StorageReadException
     * @throws \Exception
     * @throws \LowlyPHP\Exception\ConfigException
     * @codeCoverageIgnore
     */
    protected function connect() : void
    {
        $this->validate();

        $this->connection = new \PDO(
            sprintf(
                'mysql:host=%s;dbname=%s;port=%d;charset=%s',
                $this->config[self::CONFIG_HOST],
                $this->config[self::CONFIG_NAME],
                (int) $this->config[self::CONFIG_PORT],
                $this->config[self::CONFIG_CHARSET]
            ),
            $this->config[self::CONFIG_USER],
            $this->config[self::CONFIG_PASS]
        );

        $this->connection->setAttribute(\PDO::ATTR_PERSISTENT, true);
        $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->prepare();
    }

    /**
     * Describe the given table schema.
     *
     * @param string $table
     * @return array
     */
    protected function describeTable(string $table) : array
    {
        /** @var \PDOStatement $statement */
        $statement = $this->connection->prepare(
            \sprintf(
                "SELECT *
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = '%s'
                AND TABLE_NAME = '%s'",
                $this->config[self::CONFIG_NAME],
                $table
            )
        );

        $statement->execute();

        return (array) $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Generate a mock of the current schema applied to the current table.
     *
     * @return SchemaInterface
     * @throws \LowlyPHP\Exception\ConfigException
     */
    protected function mockActualSchema() : SchemaInterface
    {
        /** @var array $descriptor */
        $descriptor = $this->describeTable($this->config[self::CONFIG_TABLE]);
        /** @var ColumnInterface[] $columns */
        $columns = [];

        foreach ($descriptor as $column) {
            \preg_match('/[^(]*(\(([0-9,]+)\))/', $column['COLUMN_TYPE'], $typeInfo);
            $length = \trim((string) \end($typeInfo));

            $columns[] = $this->columnFactory->create(
                $column['COLUMN_NAME'],
                \strlen($length) ? $length : '0',
                $this->typeMap[\strtolower($column['DATA_TYPE'])] ?? ColumnInterface::TYPE_STRING,
                []
            );
        }

        return $this->schemaFactory->create(
            SchemaInterface::DEFAULT_NAME,
            $this->config[self::CONFIG_TABLE],
            $columns
        );
    }

    /**
     * Auto-generate the storage table if required.
     *
     * @throws \LowlyPHP\Exception\ConfigException
     * @codeCoverageIgnore
     * @throws \Exception
     */
    protected function prepare() : void
    {
        $state = [false, false];
        if (!$this->tableExists($this->config[self::CONFIG_TABLE])) {
            /** @var \PDOStatement $statement */
            $statement = $this->connection->prepare(
                $this->schemaConverter->convert($this->schema)
            );

            $statement->execute();
            $state[0] = true;
        } else {
            $state[0] = true;
        }

        if ($this->schema && !$this->schemaExists($this->config[self::CONFIG_TABLE], $this->schema)) {
            /** @var string[] $diff */
            $diff = $this->schemaConverter->diff(
                $this->mockActualSchema(),
                $this->schema
            );

            foreach ($diff as $sql) {
                /** @var \PDOStatement $statement */
                $statement = $this->connection->prepare($sql);
                $statement->execute();

                if ((int) $statement->errorCode() > 0) {
                    throw new \Exception('SQL Error: ' . \implode(':', $statement->errorInfo()));
                }
            }

            $state[1] = true;
        } else {
            $state[1] = true;
        }

        $statement = null;
        $this->isPrepared = \count(\array_filter($state)) === 2;
    }

    /**
     * Determine whether the given schema is applied to the table.
     *
     * @todo Does not support column type, length, index, or relationship analysis.
     * @param string $table
     * @param SchemaInterface $schema
     * @return bool
     */
    protected function schemaExists(string $table, SchemaInterface $schema) : bool
    {
        $actual = $this->describeTable($table);
        /** @var ColumnInterface[] $expected */
        $expected = $schema->getColumns();

        foreach ($expected as $column) {
            if (!isset($actual[$column->getName()])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Initialize the connection details.
     *
     * @param bool $reconnect
     * @throws \LowlyPHP\Exception\ConfigException
     * @throws StorageReadException
     */
    protected function setConnection($reconnect = true) : void
    {
        $this->config = array_merge(
            $this->config,
            $this->app->config(
                \sprintf('connections.%s', $this->connectionName)
            )
        );

        if ($reconnect && $this->connection) {
            $this->connection = null;
            $this->connect();
        }
    }

    /**
     * Determine whether the given table exists.
     *
     * @param string $table
     * @return bool
     */
    protected function tableExists(string $table) : bool
    {
        /** @var \PDOStatement $statement */
        $statement = $this->connection->prepare(
            \sprintf(
                "SELECT EXISTS (
                    SELECT * FROM INFORMATION_SCHEMA.TABLES
                    WHERE TABLE_SCHEMA = '%s'
                    AND TABLE_NAME = '%s'
                )",
                $this->config[self::CONFIG_NAME],
                $table
            )
        );

        $statement->execute();

        return (bool) $statement->fetchColumn();
    }

    /**
     * Validate the configuration.
     *
     * @return bool
     * @throws StorageReadException
     * @codeCoverageIgnore
     */
    protected function validate() : bool
    {
        if (empty($this->config[self::CONFIG_HOST])) {
            throw new StorageReadException('No host configured.');
        }

        if (empty($this->config[self::CONFIG_NAME])) {
            throw new StorageReadException('No database configured.');
        }

        if (empty($this->config[self::CONFIG_TABLE])) {
            throw new StorageReadException('No database table configured.');
        }

        return true;
    }

    /**
     * Prepare SQL conditions for the given filters.
     *
     * @param array $filters
     * @return string[]
     */
    private function prepareConditions(array $filters) : array
    {
        $conditions = [];

        /** @var \LowlyPHP\Service\Api\FilterInterface $filter */
        foreach (\array_values($filters) as $index => $filter) {
            $value = $this->conditionProcessorPool->process(
                $this->connection->quote($filter->getValue()),
                $filter,
                $this->schema->getColumn($filter->getField()),
                $this->connection
            );

            $conditions[] = \trim(
                \sprintf(
                    '%s %s %s %s',
                    $index > 0 ? $filter->getOperator() : '',
                    $filter->getField(),
                    $filter->getComparator(),
                    $value
                )
            );
        }

        return $conditions;
    }
}
