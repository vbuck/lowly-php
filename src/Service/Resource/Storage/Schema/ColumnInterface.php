<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Service\Resource\Storage\Schema;

/**
 * This interface provides a column descriptor for {@see \LowlyPHP\Service\Resource\Storage\SchemaInterface}.
 *
 * A column represents one data property of a record in storage.
 */
interface ColumnInterface
{
    const TYPE_BOOL = 'bool';
    const TYPE_FLOAT = 'float';
    const TYPE_INT = 'int';
    const TYPE_STRING = 'text';
    const TYPE_SERIALIZED = 'serialized';
    const TYPE_COMPOUND = 'compound';

    /**
     * Get the value length of the column.
     *
     * @return string
     */
    public function getLength() : string;

    /**
     * Get extra meta-data associated with the column.
     *
     * @return array
     */
    public function getMetadata() : array;

    /**
     * Get the name of the column.
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Get the value type of the column.
     *
     * @return string
     */
    public function getType() : string;
}
