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
 * This interface defines a generic data entity.
 */
interface EntityInterface
{
    const ID = 'entity_id';

    /**
     * Export all instance data as an array.
     *
     * Keys should be a snake-case equivalent of its getter method; eg: getEntityId -> entity_id
     * Values must be converted to scalar or serialized data.
     *
     * Note that storage drivers rely on export behavior to store entity data.
     * Properties not mapped to export may not be committed to storage.
     *
     * @see \LowlyPHP\Service\Resource\SerializerInterface
     * @see \LowlyPHP\Service\Resource\SerializableInterface
     * @return array
     */
    public function export() : array;

    /**
     * Get the record ID of the entity.
     *
     * @return int
     */
    public function getEntityId() : int;

    /**
     * Set the record ID of the entity.
     *
     * @param int $id
     */
    public function setEntityId(int $id) : void;
}
