<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Service\Resource\Storage\Schema;

use LowlyPHP\Service\Resource\Storage\SchemaInterface;

/**
 * This interface provides a conversion path from framework storage schema to driver-specific schema.
 */
interface ConverterInterface
{
    /**
     * Convert the schema to a driver-specific format.
     *
     * @param SchemaInterface $schema
     * @return string
     */
    public function convert(SchemaInterface $schema) : string;

    /**
     * Generate a column diff between two schemas.
     *
     * @param SchemaInterface $left
     * @param SchemaInterface $right
     * @return string[]|ColumnInterface[] An array of statements or column data to achieve the differing state.
     */
    public function diff(SchemaInterface $left, SchemaInterface $right) : array;
}
