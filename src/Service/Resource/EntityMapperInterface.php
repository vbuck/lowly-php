<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Service\Resource;

/**
 * This interface manages the hydration mapping for entities.
 *
 * Mapping is the process of transferring a simple set of data to its target entity properties.
 */
interface EntityMapperInterface
{
    /**
     * Map the given data set to the given entity.
     *
     * @param array $data
     * @param EntityInterface $entity
     * @throws \InvalidArgumentException
     */
    public function map(array $data, EntityInterface $entity) : void;
}
