<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Resource\Storage\Schema;

use LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface;

/**
 * Schema column implementation for {@see \LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface}.
 */
class Column implements ColumnInterface
{
    private $length = '0';
    private $name = '';
    private $metadata = [];
    private $type = '';

    /**
     * @param string $name
     * @param string $length
     * @param string $type
     * @param array $metadata
     * @codeCoverageIgnore
     */
    public function __construct(
        string $name,
        string $length = '0',
        string $type = ColumnInterface::TYPE_STRING,
        array $metadata = []
    ) {
        $this->name = $name;
        $this->length = $length;
        $this->type = $type;
        $this->metadata = $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getLength() : string
    {
        return $this->length;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata() : array
    {
        return $this->metadata;
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
    public function getType() : string
    {
        return $this->type;
    }
}
