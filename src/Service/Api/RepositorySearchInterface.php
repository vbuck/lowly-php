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
 * This interface defines the searchable criteria for records via {@see RepositoryInterface::search()}. It is expected
 * that a factory is used to generate these instances for use in repository search queries.
 */
interface RepositorySearchInterface
{
    const DEFAULT_LIMIT = 250;
    const KEY_FILTERS = 'filters';
    const KEY_LIMIT = 'limit';
    const KEY_PAGE = 'page';
    const MAX_LIMIT = 1000;

    /**
     * Add a filter to the search criteria.
     *
     * @param string $key The property name to search.
     * @param string $value The search term for the given property.
     * @param string $comparator The type of comparison operator on the given term.
     * @param string $operator The grouping operator with preceding filters.
     * @return RepositorySearchInterface
     * @throws \InvalidArgumentException
     */
    public function addFilter(
        string $key,
        string $value,
        string $comparator = FilterInterface::COMPARATOR_EQ,
        string $operator = FilterInterface::OPERATOR_AND
    ) : RepositorySearchInterface;

    /**
     * Export the search criteria as an array.
     *
     * Output must be grouped by available types: filters, limit, page; corresponds with data key constants. Callers are
     * responsible for type-checking this format and routing requests based on the presence of group members.
     *
     * @return array
     */
    public function export() : array;

    /**
     * Get an existing filter by key.
     *
     * @param string $key
     * @return FilterInterface|null
     */
    public function getFilter(string $key);

    /**
     * Get all filters.
     *
     * @return FilterInterface[]
     */
    public function getFilters() : array;

    /**
     * Get the result record limit.
     *
     * @return int
     */
    public function getLimit() : int;

    /**
     * Get the result page number.
     *
     * @return int
     */
    public function getPage() : int;

    /**
     * Manually add a filter instance to the criteria.
     *
     * @param string $key
     * @param FilterInterface $filter
     * @return RepositorySearchInterface
     */
    public function setFilter(string $key, FilterInterface $filter) : RepositorySearchInterface;

    /**
     * Set or replace all filters.
     *
     * @param FilterInterface[] $filters
     * @return RepositorySearchInterface
     */
    public function setFilters(array $filters) : RepositorySearchInterface;

    /**
     * Set the result record limit.
     *
     * @param int $limit
     * @return RepositorySearchInterface
     */
    public function setLimit(int $limit) : RepositorySearchInterface;

    /**
     * Set the result page number.
     *
     * @param int $page
     * @return RepositorySearchInterface
     */
    public function setPage(int $page) : RepositorySearchInterface;

    /**
     * Remove a filter from the criteria.
     *
     * @param string $key
     */
    public function unsetFilter(string $key) : void;
}
