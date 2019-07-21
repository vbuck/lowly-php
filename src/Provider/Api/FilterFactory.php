<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Api;

use LowlyPHP\ApplicationManager;
use LowlyPHP\Exception\ConfigException;
use LowlyPHP\Service\Api\FilterInterface;

/**
 * Factory for {@see \LowlyPHP\Service\Api\RepositorySearchInterface} filter instances.
 */
class FilterFactory
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
     * @param string $type
     * @return FilterInterface
     * @throws ConfigException
     */
    public function create(string $type = FilterInterface::class) : FilterInterface
    {
        return $this->app->createObject($type);
    }
}
