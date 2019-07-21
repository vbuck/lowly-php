<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Service\Resource\Storage;

use LowlyPHP\Service\Resource\EntityInterface;

/**
 * This interface manages entity schema generation.
 *
 * Schema is used to instruct the storage driver to read and write on data sources assigned to the entity.
 */
interface SchemaMapperInterface
{
    /**
     * Map the given entity to its schema.
     *
     * @param EntityInterface $entity
     * @return SchemaInterface
     * @throws \LowlyPHP\Exception\ConfigException
     */
    public function map(EntityInterface $entity) : SchemaInterface;
}
