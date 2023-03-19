<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2023 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Resource\Storage\Schema\Column\ConditionProcessor;

use LowlyPHP\Service\Resource\Storage\Schema\Column\ConditionProcessorInterface;
use LowlyPHP\Service\Api\FilterInterface;
use LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface;

/**
 * NULL/NOT NULL condition processor for columns.
 */
class NullNotNull implements ConditionProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function execute(string $value, FilterInterface $filter, ColumnInterface $column, \PDO $connection) : string
    {
        return \in_array(
            $filter->getComparator(),
            [FilterInterface::COMPARATOR_NOT_NULL, FilterInterface::COMPARATOR_NULL]
        ) ? '' : $connection->quote((string) $filter->getValue());
    }
}
