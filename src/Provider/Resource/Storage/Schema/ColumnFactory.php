<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Resource\Storage\Schema;

use LowlyPHP\ApplicationManager;
use LowlyPHP\Service\Resource\Storage\Schema\ColumnInterface;

/**
 * Factory for {@see ColumnInterface} instances.
 */
class ColumnFactory
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
     * @param string $length
     * @param string $type
     * @param array $metadata
     * @return ColumnInterface
     * @throws \LowlyPHP\Exception\ConfigException
     */
    public function create(
        string $name,
        string $length = '0',
        string $type = ColumnInterface::TYPE_STRING,
        array $metadata = []
    ) : ColumnInterface
    {
        return $this->app->createObject(
            ColumnInterface::class,
            [
                'name' => $name,
                'length' => $length,
                'type' => $type,
                'metadata' => $metadata,
            ]
        );
    }
}
