<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Resource\Storage\Schema\Converter;

use LowlyPHP\ApplicationManager;
use LowlyPHP\Exception\ConfigException;
use LowlyPHP\Provider\Resource\Storage\Schema\ColumnFactory;
use LowlyPHP\Provider\Resource\Storage\SchemaFactory;
use LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface;
use LowlyPHP\Service\Resource\Storage\Schema\ConverterInterface;
use LowlyPHP\Service\Resource\Storage\SchemaInterface;
use LowlyPHP\Service\Resource\Storage\SchemaMapperInterface;
use LowlyPHP\Service\Resource\Storage\SchemaStorageInterface;

/**
 * SQL driver converter implementation for {@see ConverterInterface}.
 */
class Sql implements ConverterInterface
{
    /** @var ColumnFactory */
    protected $columnFactory;

    /** @var SchemaFactory */
    protected $schemaFactory;

    /** @var SchemaMapperInterface */
    protected $schemaMapper;

    /** @var ApplicationManager */
    private $app;

    /**
     * @param SchemaFactory $schemaFactory
     * @param ColumnFactory $columnFactory
     * @param SchemaMapperInterface $schemaMapper
     * @param ApplicationManager|null $app
     */
    public function __construct(
        SchemaFactory $schemaFactory,
        ColumnFactory $columnFactory,
        SchemaMapperInterface $schemaMapper,
        ApplicationManager $app = null
    ) {
        $this->app = $app ?? ApplicationManager::getInstance();
        $this->schemaFactory = $schemaFactory;
        $this->columnFactory = $columnFactory;
        $this->schemaMapper = $schemaMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(SchemaInterface $schema) : string
    {
        $columns = [];
        $output = \sprintf(
            "CREATE TABLE `%s` (\n", $schema->getSource()
        );

        /** @var ColumnInterface $column */
        foreach ($schema->getColumns() as $column) {
            $columns[] = $this->getColumnDefinition($column);
        }

        $relationships = $this->getRelationshipDefinitions($schema);
        $indexes = $this->getIndexDefinitions($schema);
        $groups = \array_filter(
            [
                \implode(",\n", $columns),
                \implode(",\n", $relationships),
                \implode(",\n", $indexes),
            ]
        );

        $output .= \implode(",\n", $groups);
        $output .= "\n);";

        return $output;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LowlyPHP\Exception\ConfigException
     */
    public function diff(SchemaInterface $left, SchemaInterface $right) : array
    {
        /** @var string[] $columns */
        $columns = [];
        /** @var ColumnInterface|null $candidate */
        $candidate = null;
        $exists = false;

        /** @var ColumnInterface $rightColumn */
        foreach ($right->getColumns() as $rightColumn) {
            $candidate = null;

            /** @var ColumnInterface $leftColumn */
            foreach ($left->getColumns() as $leftColumn) {
                $candidate = $rightColumn;
                $exists = false;

                if ($rightColumn->getName() === $leftColumn->getName()
                    && $rightColumn->getType() === $leftColumn->getType()
                    && $rightColumn->getLength() === $leftColumn->getLength()
                    // @todo Does not currently support length or metadata comparison
                    //&& \json_encode($leftColumn->getMetadata()) === \json_encode($rightColumn->getMetadata())
                ) {
                    $candidate = null;
                    $exists = true;
                    break;
                }
            }

            if ($candidate) {
                $columns[] = \sprintf(
                    "ALTER TABLE %s %s COLUMN %s",
                    $right->getSource(),
                    $exists ? 'MODIFY' : 'ADD',
                    $this->getColumnDefinition($candidate)
                );
            }
        }

        return $columns;
    }

    /**
     * Generate an index name for the given data set.
     *
     * @param string $table
     * @param string[] $columns
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function createIndexName(string $table, array $columns) : string
    {
        if (empty($columns)) {
            throw new \InvalidArgumentException('Index name generation requires at least 1 column.');
        }

        $key = $table . ';' . \implode(':', $columns);

        return 'IDX_' . \sha1($key);
    }

    /**
     * Generate a column definition as a SQL statement partial.
     *
     * @param ColumnInterface $column
     * @return string
     */
    protected function getColumnDefinition(ColumnInterface $column) : string
    {
        $metadata = $column->getMetadata();
        $default = isset($metadata[SchemaStorageInterface::META_KEY_DEFAULT_VALUE])
            ? (string) $metadata[SchemaStorageInterface::META_KEY_DEFAULT_VALUE]
            : null;

        return \trim(
            \sprintf(
                "`%s` %s%s %s%s",
                $column->getName(),
                $this->getColumnType($column->getType(), $column->getLength()),
                $column->getLength() ? "({$column->getLength()})" : '',
                !empty($metadata[SchemaStorageInterface::META_KEY_IDENTIFIER]) ? $this->getPrimaryKeyAttribute() : '',
                $default ? ('DEFAULT ' . ($default === 'NULL' ? $default : ("'" . addslashes($default) . "'"))) : ''
            )
        );
    }

    /**
     * Get the corresponding SQL column type for the given type.
     *
     * @param string $input
     * @param string|null $length
     * @return string
     */
    protected function getColumnType(string $input, string $length = null) : string
    {
        switch ($input) {
            case ColumnInterface::TYPE_BOOL :
                return 'TINYINT';
            case ColumnInterface::TYPE_FLOAT :
                return 'DECIMAL';
            case ColumnInterface::TYPE_INT :
                return 'INT';
            case ColumnInterface::TYPE_STRING :
            case ColumnInterface::TYPE_SERIALIZED :
            case ColumnInterface::TYPE_COMPOUND :
            default :
                if ((int) $length > 0) {
                    return 'VARCHAR';
                }

                return 'TEXT';
        }
    }

    /**
     * Generate index definitions as SQL statement partials.
     *
     * @param SchemaInterface $schema
     * @return array
     */
    protected function getIndexDefinitions(SchemaInterface $schema) : array
    {
        /** @var array $groups */
        $groups = [
            'default' => [],
            'unique' => [],
        ];

        /** @var ColumnInterface $column */
        foreach ($schema->getColumns() as $column) {
            /** @var array $metadata */
            $metadata = $column->getMetadata();

            if (!isset($metadata[SchemaStorageInterface::META_KEY_INDEXES])) {
                continue;
            }

            foreach ((array) $metadata[SchemaStorageInterface::META_KEY_INDEXES] as $index) {
                if (empty($index)) {
                    continue;
                }

                $isUnique = (bool) ($index['unique'] ?? false);
                $groups[($isUnique ? 'unique' : 'default')][] = $column->getName();
            }
        }

        $groups = \array_filter($groups);

        return \array_map(
            function ($group, $type) use ($schema) {
                return \sprintf(
                    "%s INDEX %s (%s)",
                    $type === 'unique' ? 'UNIQUE' : '',
                    $this->createIndexName($schema->getSource(), $group),
                    \implode(', ', $group)
                );
            },
            $groups,
            \array_keys($groups)
        );
    }

    /**
     * Get the primary key attribute definition.
     *
     * @return string
     */
    protected function getPrimaryKeyAttribute() : string
    {
        return 'AUTO_INCREMENT PRIMARY KEY';
    }

    /**
     * Generate relationship constraints as SQL statement partials.
     *
     * @param SchemaInterface $schema
     * @return string[]
     * @throws ConfigException
     */
    protected function getRelationshipDefinitions(SchemaInterface $schema) : array
    {
        /** @var string[] $relationships */
        $relationships = [];

        /** @var ColumnInterface $column */
        foreach ($schema->getColumns() as $column) {
            /** @var array $metadata */
            $metadata = $column->getMetadata();

            if (!isset($metadata[SchemaStorageInterface::META_KEY_RELATIONSHIPS])) {
                continue;
            }

            foreach ((array) $metadata[SchemaStorageInterface::META_KEY_RELATIONSHIPS] as $relationship) {
                if (empty($relationship) || \count($relationship) < 2) {
                    continue;
                }

                /** @var \LowlyPHP\Service\Resource\EntityInterface|null $relatedEntity */
                try {
                    $relatedEntity = $this->app->getObject($relationship[0]);
                } catch (ConfigException $error) {
                    continue;
                }

                if (empty($relatedEntity)) {
                    continue;
                }

                /** @var SchemaInterface $relatedSchema */
                $relatedSchema = $this->schemaMapper->map($relatedEntity);
                $relationships[] = \sprintf(
                    "FOREIGN KEY (`%s`) REFERENCES `%s`(`%s`) ON DELETE CASCADE ON UPDATE CASCADE",
                    $column->getName(),
                    $relatedSchema->getSource(),
                    $relationship[1]
                );
            }
        }

        return $relationships;
    }
}
