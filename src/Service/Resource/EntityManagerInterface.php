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
 * This interface defines the service layer for entity CRUD operations.
 *
 * It may use {@see \LowlyPHP\Service\Resource\StorageInterface} drivers to facilitate read and write activity.
 */
interface EntityManagerInterface
{
    /**
     * Commit the data state of the given entity to storage. If no entity is given, commit all managed entities.
     *
     * @param EntityInterface|null $entity
     * @throws \LowlyPHP\Exception\StorageWriteException
     */
    public function flush(EntityInterface $entity = null) : void;

    /**
     * Refresh the data state of the given entity by reloading its data from storage.
     *
     * Alternatively, a data set can be provided, in which case hydration must map it to the entity.
     *
     * @param EntityInterface $entity
     * @param array|null $data
     * @param bool $strict Set whether hydration should be allowed without a database record match
     * @throws \LowlyPHP\Exception\StorageReadException
     */
    public function hydrate(EntityInterface $entity, array $data = null, bool $strict = false) : void;

    /**
     * Persist the data state of the given entity with storage.
     *
     * @param EntityInterface $entity
     * @throws \LowlyPHP\Exception\StorageWriteException
     * @throws \LowlyPHP\Exception\StorageReadException
     */
    public function persist(EntityInterface $entity) : void;

    /**
     * Remove the entity data from storage.
     *
     * @param EntityInterface $entity
     * @throws \LowlyPHP\Exception\StorageWriteException
     */
    public function remove(EntityInterface $entity) : void;
}
