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
use LowlyPHP\Service\Api\RepositorySearchResultInterface;

/**
 * Factory for {@see \LowlyPHP\Service\Api\RepositorySearchResultInterface} instances.
 */
class RepositorySearchResultFactory
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
     * @param array $items
     * @param RepositorySearchInterface $criteria
     * @param int|null $totalRecords
     * @return RepositorySearchResultInterface
     * @throws ConfigException
     */
    public function create(
        array $items,
        RepositorySearchInterface $criteria,
        int $totalRecords = null
    ) : RepositorySearchResultInterface
    {
        return $this->app->createObject(
            RepositorySearchResultInterface::class,
            [
                'items' => $items,
                'criteria' => $criteria,
                'totalRecords' => $totalRecords,
            ]
        );
    }
}
