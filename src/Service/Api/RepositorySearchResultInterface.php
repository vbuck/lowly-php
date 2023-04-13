<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Service\Api;

/**
 * This interface defines the result set for a repository search operation.
 */
interface RepositorySearchResultInterface
{
    /**
     * Get the total number of items in the result set.
     *
     * @return int
     */
    public function count() : int;

    /**
     * Get the original criteria used to generate the result set.
     *
     * @return RepositorySearchInterface
     */
    public function getCriteria() : RepositorySearchInterface;

    /**
     * Retrieve all items in the result set.
     *
     * @return \LowlyPHP\Service\Resource\EntityInterface[]
     */
    public function getItems() : array;

    /**
     * Get the total number of records available in the repository.
     *
     * @return null|int Returns null if the information is not available.
     */
    public function getTotalRecords() : ?int;
}
