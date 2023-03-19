<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2023 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Resource\Storage\Schema\Column;

use LowlyPHP\ApplicationManager;
use LowlyPHP\Service\Api\FilterInterface;
use LowlyPHP\Service\Resource\Storage\Schema\Column\ConditionProcessorPoolInterface;
use LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface;

class ConditionProcessorPool implements ConditionProcessorPoolInterface
{
    /** @var ApplicationManager */
    private $appManager;

    /**
     * @param ApplicationManager $appManager
     */
    public function __construct(ApplicationManager $appManager)
    {
        $this->appManager = $appManager;
    }

    /**
     * @inheritdoc
     * @throws \LowlyPHP\Exception\ConfigException
     */
    public function process(string $value, FilterInterface $filter, ColumnInterface $column, \PDO $connection) : string
    {
        foreach ((array) $this->appManager->config('schema_management.conditions') as $class) {
            /** @var \LowlyPHP\Service\Resource\Storage\Schema\Column\ConditionProcessorInterface $processor */
            $processor = $this->appManager->getObject($class);
            $value = $processor->execute($value, $filter, $column, $connection);
        }

        return $value;
    }
}