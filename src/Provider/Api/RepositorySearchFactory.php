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
use LowlyPHP\Service\Api\RepositorySearchInterface;

/**
 * Factory for {@see \LowlyPHP\Service\Api\RepositorySearchInterface} instances.
 */
class RepositorySearchFactory
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
     * Create a new search criteria object.
     *
     * @param string $type
     * @return RepositorySearchInterface
     * @throws ConfigException
     */
    public function create(string $type = RepositorySearchInterface::class) : RepositorySearchInterface
    {
        return $this->app->createObject($type);
    }
}
