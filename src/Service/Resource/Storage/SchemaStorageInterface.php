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
 * This interface provides schema-based storage information to drivers.
 */
interface SchemaStorageInterface
{
    /**
     * Default value property.
     *
     * Used to define the default column value when none is present on record update.
     *
     * Type: string
     * Usage: [SchemaStorageInterface::META_KEY_DEFAULT_VALUE => '0']
     */
    const META_KEY_DEFAULT_VALUE = 'default_value';

    /**
     * Identity property.
     *
     * Used to define which column(s) is the primary ID.
     *
     * Type: bool
     * Usage: [SchemaStorageInterface::META_KEY_IDENTIFIER => true]
     */
    const META_KEY_IDENTIFIER = 'identifier';

    /**
     * Index property.
     *
     * Used to define index details about a column(s). Driver specific.
     *
     * Type: array[]
     * Usage: [SchemaStorageInterface::META_KEY_INDEXES => [['unique' => true]]]
     */
    const META_KEY_INDEXES = 'indexes';

    /**
     * Relationships property.
     *
     * Used to define relationships between two or more source's columns. Driver specific.
     *
     * Type: array[]
     * Usage: [SchemaStorageInterface::META_KEY_RELATIONSHIPS => [[EntityInterface::class, EntityInterface::ID]]]
     */
    const META_KEY_RELATIONSHIPS = 'relationships';

    /**
     * Set the current storage schema.
     *
     * Implementing classes will reference the schema to manage storage driver-specific features.
     *
     * @param SchemaInterface $schema
     */
    public function setSchema(SchemaInterface $schema) : void;
}
