<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Service\Resource\Storage;

/**
 * This interface provides a descriptor for storage schema.
 *
 * Schema is a cross-driver compatible syntax for data storage.
 */
interface SchemaInterface
{
    const DEFAULT_NAME = 'default';

    /**
     * Get the column definitions for the schema.
     *
     * Columns comprise the table-based structure of the schema.
     *
     * @return \LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface[]
     */
    public function getColumns() : array;

    /**
     * Get the name of the schema.
     *
     * A name is an identifier for the schema.
     *
     * @return string
     */
    public function getName() : string;

    /**
     * Get the schema source attribute.
     *
     * A source is a descriptor of the storage path associated with the schema.
     *
     * @return string
     */
    public function getSource() : string;
}
