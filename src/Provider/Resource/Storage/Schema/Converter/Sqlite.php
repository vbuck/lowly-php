<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Resource\Storage\Schema\Converter;

use LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface;
use LowlyPHP\Service\Resource\Storage\SchemaStorageInterface;

/**
 * SQLite driver converter implementation for {@see ConverterInterface}.
 */
class Sqlite extends Sql
{
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
                $this->getColumnType($column->getType(), ''),
                '',
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
                return 'INTEGER';
            case ColumnInterface::TYPE_FLOAT :
                return 'REAL';
            case ColumnInterface::TYPE_INT :
                return 'INTEGER';
            case ColumnInterface::TYPE_STRING :
            case ColumnInterface::TYPE_SERIALIZED :
            case ColumnInterface::TYPE_COMPOUND :
            default :
                return 'TEXT';
        }
    }

    protected function getPrimaryKeyAttribute() : string
    {
        return 'PRIMARY KEY AUTOINCREMENT';
    }
}
