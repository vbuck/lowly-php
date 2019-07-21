<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Api;

use LowlyPHP\Service\Api\FilterInterface;
use LowlyPHP\Service\Api\RepositorySearchInterface;

/**
 * Search criteria implementation for {@see RepositorySearchInterface}.
 */
class RepositorySearch implements RepositorySearchInterface
{
    /** @var FilterFactory */
    private $filterFactory;

    /** @var array */
    private $filters = [];

    /** @var int */
    private $limit = self::DEFAULT_LIMIT;

    /** @var int */
    private $page = 1;

    /**
     * @param FilterFactory $filterFactory
     * @codeCoverageIgnore
     */
    public function __construct(FilterFactory $filterFactory)
    {
        $this->filterFactory = $filterFactory;
    }

    /**
     * {@inheritdoc}
     * @throws \LowlyPHP\Exception\ConfigException
     */
    public function addFilter(
        string $key,
        string $value,
        string $comparator = FilterInterface::COMPARATOR_EQ,
        string $operator = FilterInterface::OPERATOR_AND
    ) : RepositorySearchInterface
    {
        $this->filters[$key] = $this->filterFactory->create()
            ->setField($key)
            ->setValue($value)
            ->setComparator($comparator)
            ->setOperator($operator);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function export() : array
    {
        return [
            self::KEY_FILTERS => $this->filters,
            self::KEY_LIMIT => $this->limit,
            self::KEY_PAGE => $this->page,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilter(string $key)
    {
        if (isset($this->filters[$key])) {
            return $this->filters[$key];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters() : array
    {
        return $this->filters;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit() : int
    {
        return $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function getPage() : int
    {
        return $this->page;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilter(string $key, FilterInterface $filter) : RepositorySearchInterface
    {
        $this->filters[$key] = $filter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilters(array $filters) : RepositorySearchInterface
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLimit(int $limit) : RepositorySearchInterface
    {
        $this->limit = $limit < 0
            ? 0
            : ($limit <= self::MAX_LIMIT ? $limit : self::MAX_LIMIT);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPage(int $page) : RepositorySearchInterface
    {
        $this->page = $page < 1 ? 1 : $page;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function unsetFilter(string $key) : void
    {
        if (isset($this->filters[$key])) {
            unset($this->filters[$key]);
        }
    }
}
