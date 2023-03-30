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

/**
 * Generic implementation for {@see \LowlyPHP\Service\Api\FilterInterface}.
 */
class Filter implements FilterInterface
{
    /** @var string */
    private $comparator;

    /** @var array */
    private $comparators = [
        self::COMPARATOR_EQ,
        self::COMPARATOR_GT,
        self::COMPARATOR_GTE,
        self::COMPARATOR_IN_SET,
        self::COMPARATOR_LT,
        self::COMPARATOR_LTE,
        self::COMPARATOR_NEQ,
        self::COMPARATOR_NONE,
        self::COMPARATOR_LIKE,
        self::COMPARATOR_NLIKE,
        self::COMPARATOR_NOT_NULL,
        self::COMPARATOR_NULL,
    ];

    /** @var string */
    private $field;

    /** @var string */
    private $operator;

    /** @var array */
    private $operators = [
        self::OPERATOR_AND,
        self::OPERATOR_OR,
    ];

    /** @var mixed */
    private $value;

    /**
     * {@inheritdoc}
     */
    public function getComparator() : string
    {
        return $this->comparator;
    }

    /**
     * {@inheritdoc}
     */
    public function getField() : string
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperator() : string
    {
        return $this->operator;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setComparator(string $comparator) : FilterInterface
    {
        if (!\in_array($comparator, $this->comparators)) {
            throw new \InvalidArgumentException(sprintf('Unsupported comparator "%s"', $comparator));
        }

        $this->comparator = $comparator;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setField(string $field) : FilterInterface
    {
        $this->field = $field;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOperator(string $operator) : FilterInterface
    {
        if (!\in_array($operator, $this->operators)) {
            throw new \InvalidArgumentException(sprintf('Unsupported operator "%s"', $operator));
        }

        $this->operator = $operator;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(string $value) : FilterInterface
    {
        $this->value = $value;

        return $this;
    }
}
