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
use LowlyPHP\Provider\Resource\Storage\Schema\ColumnFactory;
use LowlyPHP\Provider\Resource\Storage\Schema\Converter\Sqlite as SchemaConverter;
use LowlyPHP\Provider\Resource\Storage\SchemaFactory;
use LowlyPHP\Service\Resource\SerializerInterface;
use LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface;
use LowlyPHP\Service\Resource\Storage\SchemaInterface;

/**
 * PDO-SQLite storage implementation for {@see StorageInterface}.
 */
class Sqlite extends Mysql
{
    protected $typeMap = [
        'integer' => ColumnInterface::TYPE_INT,
        'real' => ColumnInterface::TYPE_FLOAT,
        'text' => ColumnInterface::TYPE_STRING,
    ];

    /**
     * @param SchemaConverter $schemaConverter
     * @param SerializerInterface $serializer
     * @param SchemaFactory $schemaFactory
     * @param ColumnFactory $columnFactory
     * @param SchemaInterface|null $schema
     * @param ApplicationManager|null $app
     * @throws \LowlyPHP\Exception\ConfigException
     * @throws \LowlyPHP\Exception\StorageReadException
     */
    public function __construct(
        SchemaConverter $schemaConverter,
        SerializerInterface $serializer,
        SchemaFactory $schemaFactory,
        ColumnFactory $columnFactory,
        SchemaInterface $schema = null,
        ApplicationManager $app = null
    ) {
        parent::__construct($schemaConverter, $serializer, $schemaFactory, $columnFactory, $schema, $app);
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

        $this->connection = new \PDO(sprintf('sqlite:%s', $this->config[self::CONFIG_NAME]));

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
            \sprintf("PRAGMA table_info('%s')", $table)
        );

        $statement->execute();
        $descriptor = [];

        foreach ((array) $statement->fetchAll(\PDO::FETCH_ASSOC) as $column) {
            $descriptor[] = [
                'ORDINAL_POSITION' => $column['cid'] ?? null,
                'COLUMN_NAME' => $column['name'] ?? null,
                'COLUMN_TYPE' => $column['type'] ?? null,
                'DATA_TYPE' => $column['type'] ?? null,
                'IS_NULLABLE' => (bool) $column['notnull'] ?? null,
                'COLUMN_DEFAULT' => $columnn['dflt_value'] ?? null,
                'COLUMN_KEY' => (bool) ($column['pk'] ?? null) === true ? 'PRI' : '',
            ];
        }

        return $descriptor;
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
                "SELECT name FROM sqlite_master WHERE type='table' AND name = '%s'",
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
        if (empty($this->config[self::CONFIG_NAME])) {
            throw new StorageReadException('No database path configured.');
        }

        if (empty($this->config[self::CONFIG_TABLE])) {
            throw new StorageReadException('No database table configured.');
        }

        return true;
    }
}
