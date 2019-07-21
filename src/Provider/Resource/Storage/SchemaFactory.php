<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Resource\Storage;

use LowlyPHP\ApplicationManager;
use LowlyPHP\Service\Resource\Storage\SchemaInterface;

/**
 * Factory for {@see SchemaInterface} instances.
 */
class SchemaFactory
{
    /** @var ApplicationManager */
    private $app;

    /**
     * @param ApplicationManager|null $app
     * @codeCoverageIgnore
     */
    public function __construct(ApplicationManager $app = null)
    {
        $this->app = $app ?? ApplicationManager::getInstance();
    }

    /**
     * Create a new filter object.
     *
     * @param string $name
     * @param string $source
     * @param \LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface[] $columns
     * @return SchemaInterface
     * @throws \LowlyPHP\Exception\ConfigException
     */
    public function create(string $name, string $source, array $columns) : SchemaInterface
    {
        return $this->app->createObject(
            SchemaInterface::class,
            [
                'name' => $name,
                'source' => $source,
                'columns' => $columns,
            ]
        );
    }
}
