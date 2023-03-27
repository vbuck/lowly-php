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
use LowlyPHP\Service\Resource\Storage\Schema\Column\ConditionProcessorInterface;
use LowlyPHP\Service\Resource\Storage\Schema\Column\ConditionProcessorPoolInterface;
use LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface;
use LowlyPHP\Service\Resource\Storage\SchemaStorageInterface;

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
        foreach ((array) $this->appManager->config('schema_management.columns.conditions') as $class) {
            /** @var ConditionProcessorInterface $processor */
            $processor = $this->appManager->getObject($class);
            $value = $processor->execute($value, $filter, $column, $connection);
        }

        /** @var ConditionProcessorInterface|null $customProcessor */
        $customProcessor = $this->getUserDefinedProcessor($column);
        if ($customProcessor) {
            $value = $customProcessor->execute($value, $filter, $column, $connection);
        }

        return $value;
    }

    /**
     * @param ColumnInterface $column
     * @return ConditionProcessorInterface|null
     */
    private function getUserDefinedProcessor(ColumnInterface $column): ?ConditionProcessorInterface
    {
        $metadata = $column->getMetadata();
        $processor = $metadata[SchemaStorageInterface::META_KEY_CONDITION_PROCESSOR] ?? false;

        if (!$processor) {
            return null;
        }

        return $processor;
    }
}