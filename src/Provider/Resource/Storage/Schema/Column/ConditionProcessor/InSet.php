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
 * IN SET condition processor for columns.
 */
class InSet implements ConditionProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function execute(string $value, FilterInterface $filter, ColumnInterface $column, \PDO $connection) : string
    {
        if ($filter->getComparator() === FilterInterface::COMPARATOR_IN_SET) {
            $value = '(' . \trim($value, '()') . ')';
        }

        return $value;
    }
}
