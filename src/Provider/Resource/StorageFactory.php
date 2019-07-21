<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Resource;

use LowlyPHP\ApplicationManager;
use LowlyPHP\Service\Resource\Storage\SchemaInterface;
use LowlyPHP\Service\Resource\StorageInterface;

/**
 * Factory for {@see StorageInterface} instances.
 *
 * Used to generate and manage storage drivers.
 */
class StorageFactory
{
    /** @var ApplicationManager */
    private $app;

    /** @var array */
    private $objects = [];

    /**
     * @param ApplicationManager|null $app
     * @codeCoverageIgnore
     */
    public function __construct(ApplicationManager $app = null)
    {
        $this->app = $app ?? ApplicationManager::getInstance();
    }

    /**
     * Create a new prepared storage driver.
     *
     * @param SchemaInterface|null $schema
     * @param string $type
     * @return StorageInterface
     * @throws \LowlyPHP\Exception\ConfigException
     */
    public function create(SchemaInterface $schema = null, string $type = StorageInterface::class) : StorageInterface
    {
        return $this->app->createObject($type, ['schema' => $schema]);
    }

    /**
     * Get a storage driver.
     *
     * @param SchemaInterface|null $schema
     * @param string $type
     * @return StorageInterface
     * @throws \LowlyPHP\Exception\ConfigException
     */
    public function get(SchemaInterface $schema = null, string $type = StorageInterface::class) : StorageInterface
    {
        $id = \sha1(
            $type . (\is_object($schema) ? \spl_object_hash($schema) : '')
        );

        if (empty($this->objects[$id])) {
            $this->objects[$id] = $this->create($schema, $type);
        }

        return $this->objects[$id];
    }
}