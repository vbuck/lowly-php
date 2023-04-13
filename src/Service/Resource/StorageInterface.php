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
 * This interface establishes a CRUD storage driver system for data.
 */
interface StorageInterface
{
    /**
     * Query storage for record counts.
     *
     * @param \LowlyPHP\Service\Api\FilterInterface[] $filters
     * @return int The total number of records matching the given criteria.
     * @throws \LowlyPHP\Exception\StorageReadException
     * @throws \InvalidArgumentException
     */
    public function count(array $filters) : int;

    /**
     * Delete a record from storage by its ID.
     *
     * @param int $id The record ID.
     * @throws \LowlyPHP\Exception\StorageWriteException
     */
    public function delete(int $id) : void;

    /**
     * Load a record from storage from its ID.
     *
     * @param int $id The record ID.
     * @return array Prepared data as key-value pairs.
     * @throws \LowlyPHP\Exception\StorageReadException
     */
    public function load(int $id) : array;

    /**
     * Query storage for records.
     *
     * @param \LowlyPHP\Service\Api\FilterInterface[] $filters
     * @param int $page The page in the result set to return as a 1-based integer.
     * @param int $limit The maximum number of records to return.
     * @return array An array of zero or more records.
     * @throws \LowlyPHP\Exception\StorageReadException
     * @throws \InvalidArgumentException
     */
    public function query(array $filters, int $page = 1, int $limit = 0) : array;

    /**
     * Write the given record data to storage.
     *
     * @param array $data The prepared data as key-value pairs.
     * @param int $id The record ID.
     * @return int The updated record ID.
     * @throws \LowlyPHP\Exception\StorageWriteException
     * @throws \InvalidArgumentException
     */
    public function save(array $data, int $id) : int;
}
