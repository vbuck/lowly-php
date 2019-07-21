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
 * This interface defines a container for generic record filter data.
 */
interface FilterInterface
{
    const COMPARATOR_EQ = '=';
    const COMPARATOR_GT = '>';
    const COMPARATOR_GTE = '>=';
    const COMPARATOR_IN_SET = 'IN';
    const COMPARATOR_LT = '<';
    const COMPARATOR_LTE = '<=';
    const COMPARATOR_NEQ = '!=';
    const COMPARATOR_LIKE = 'LIKE';
    const COMPARATOR_NLIKE = 'NOT LIKE';
    const COMPARATOR_NOT_NULL = 'IS NOT NULL';
    const COMPARATOR_NULL = 'IS NULL';
    const OPERATOR_AND = 'AND';
    const OPERATOR_OR = 'OR';

    /**
     * Get the comparison operator.
     *
     * @return string
     */
    public function getComparator() : string;

    /**
     * Get the field to filter.
     *
     * @return string
     */
    public function getField() : string;

    /**
     * Get the logical grouping operator.
     *
     * @return string
     */
    public function getOperator() : string;

    /**
     * Get the filter term.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set the comparison operator.
     *
     * @param string $comparator
     * @return FilterInterface
     * @throws \InvalidArgumentException
     */
    public function setComparator(string $comparator) : FilterInterface;

    /**
     * Set the field to filter.
     *
     * @param string $field
     * @return FilterInterface
     */
    public function setField(string $field) : FilterInterface;

    /**
     * Set the logical grouping operator.
     *
     * @param string $operator
     * @return FilterInterface
     * @throws \InvalidArgumentException
     */
    public function setOperator(string $operator) : FilterInterface;

    /**
     * Set the filter term.
     *
     * @param string $value
     * @return FilterInterface
     * @throws \InvalidArgumentException
     */
    public function setValue(string $value) : FilterInterface;
}
