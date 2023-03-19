<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Resource\Storage;

use LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface;
use LowlyPHP\Service\Resource\Storage\SchemaInterface;

/**
 * Generic schema container implementation for {@see SchemaInterface}.
 */
class Schema implements SchemaInterface
{
    /** @var ColumnInterface[] */
    protected $columns;

    /** @var string */
    protected $name;

    /** @var string */
    protected $source;

    /**
     * @param string $name
     * @param string $source
     * @param ColumnInterface[] $columns
     * @throws \InvalidArgumentException
     * @codeCoverageIgnore
     */
    public function __construct(string $name, string $source, array $columns)
    {
        $this->name = $name;
        $this->source = $source;

        foreach ($columns as $column) {
            if (!($column instanceof ColumnInterface)) {
                throw new \InvalidArgumentException(
                    sprintf('Column must be an instance of %s.', ColumnInterface::class)
                );
            }
        }

        $this->columns = $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns() : array
    {
        return $this->columns;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource() : string
    {
        return $this->source;
    }

    /**
     * @inheritdoc
     */
    public function getColumn(string $key) : ColumnInterface
    {
        foreach ($this->columns as $column) {
            if ($column->getName() === $key) {
                return $column;
            }
        }

        throw new \InvalidArgumentException(\sprintf('Column "%s" does not exist.', $key));
    }
}
