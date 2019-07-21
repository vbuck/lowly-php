<?php

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @license   MIT
 */

namespace Vbuck\LicenseManager;

/**
 * Standalone autoloader utility.
 */

use LowlyPHP\ApplicationManager;

require_once __DIR__ . DIRECTORY_SEPARATOR
    . 'src' . DIRECTORY_SEPARATOR
    . 'ApplicationManager.php';

\spl_autoload_register([ApplicationManager::class, 'autoload']);
