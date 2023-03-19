<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2023 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Service\Resource\Storage\Schema\Column;

use LowlyPHP\Service\Api\FilterInterface;
use LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface;

/**
 * An interface for executing multiple condition processors sequentially.
 */
interface ConditionProcessorPoolInterface
{
    /**
     * @param string $value
     * @param FilterInterface $filter
     * @param ColumnInterface $column
     * @param \PDO $connection
     * @return string The output condition value.
     */
    public function process(
        string $value,
        FilterInterface $filter,
        ColumnInterface $column,
        \PDO $connection
    ) : string;
}
