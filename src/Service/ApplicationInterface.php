<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Service;

use LowlyPHP\ApplicationManager;

/**
 * Application interface. Defines minimum functionality to bootstrap the application.
 */
interface ApplicationInterface
{
    /**
     * Run the application.
     *
     * @param ApplicationManager|null $app
     */
    public function run(ApplicationManager $app = null) : void;
}
