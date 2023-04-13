<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Api;

use LowlyPHP\Service\Api\RepositorySearchInterface;
use LowlyPHP\Service\Api\RepositorySearchResultInterface;

/**
 * Search criteria result implementation for {@see RepositorySearchResultInterface}.
 */
class RepositorySearchResult implements RepositorySearchResultInterface
{
    /** @var RepositorySearchInterface */
    private $criteria;

    /** @var \LowlyPHP\Service\Resource\EntityInterface[]  */
    private $items;

    /** @var int|null */
    private $totalRecords;

    /**
     * @param array $items
     * @param RepositorySearchInterface $criteria
     * @param int|null $totalRecords
     */
    public function __construct(
        array $items,
        RepositorySearchInterface $criteria,
        int $totalRecords = null
    ) {
        $this->items = $items;
        $this->criteria = $criteria;
        $this->totalRecords = $totalRecords;
    }

    /**
     * @inheritdoc
     */
    public function count() : int
    {
        return \count($this->items);
    }

    /**
     * @inheritdoc
     */
    public function getCriteria() : RepositorySearchInterface
    {
        return $this->criteria;
    }

    /**
     * @inheritdoc
     */
    public function getItems() : array
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function getTotalRecords() : ?int
    {
        return $this->totalRecords;
    }
}
