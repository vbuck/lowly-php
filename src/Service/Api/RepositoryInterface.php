<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Service\Api;

use LowlyPHP\Service\Resource\EntityInterface;

/**
 * This interface defines a CRUD service contract for entity management.
 */
interface RepositoryInterface
{
    /**
     * Create an entity.
     *
     * @param array $data
     * @return EntityInterface
     * @throws \LowlyPHP\Exception\InvalidEntityException
     * @throws \LowlyPHP\Exception\EntityExistsException
     * @throws \LowlyPHP\Exception\StorageWriteException
     */
    public function create(array $data = []) : EntityInterface;

    /**
     * Delete the given entity from storage. Deletion is discouraged. Use deactivation when possible.
     *
     * @param EntityInterface $entity
     * @throws \LowlyPHP\Exception\StorageWriteException
     */
    public function delete(EntityInterface $entity) : void;

    /**
     * Search the repository by the given criteria and return all matching records.
     *
     * @param RepositorySearchInterface $criteria
     * @return RepositorySearchResultInterface
     * @throws \LowlyPHP\Exception\StorageReadException
     */
    public function list(RepositorySearchInterface $criteria) : RepositorySearchResultInterface;

    /**
     * Lookup an entity by the given record ID.
     *
     * @param string $id
     * @param int|null $scopeId
     * @return EntityInterface
     * @throws \LowlyPHP\Exception\InvalidEntityException
     * @throws \LowlyPHP\Exception\StorageReadException
     */
    public function read(string $id, int $scopeId = null) : EntityInterface;

    /**
     * Commit the given entity data to storage.
     *
     * @param EntityInterface $entity
     * @throws \LowlyPHP\Exception\InvalidEntityException
     * @throws \LowlyPHP\Exception\StorageWriteException
     */
    public function update(EntityInterface $entity) : void;
}
