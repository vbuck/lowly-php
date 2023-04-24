<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP;

use LowlyPHP\Exception\ConfigException;

/**
 * Application configuration locator service.
 */
class ConfigResolver
{
    /**
     * Environment can be initialized with a custom configuration path if using a non-standard setup.
     */
    const ENV_CONFIG_PATH = 'LOWLYPHP_APP_CONFIG_PATH';

    const FILENAME = 'config.json';

    /**
     * Get the default configuration file path.
     *
     * @return string
     */
    public function getDefaultPath() : string
    {
        return (string) \realpath(
            \dirname(__DIR__) . DIRECTORY_SEPARATOR . self::FILENAME . '.dist'
        );
    }

    /**
     * Locate the application configuration file.
     *
     * @param string $path
     * @return string
     * @throws ConfigException
     */
    public function resolve(string $path = '') : string
    {
        if (empty($path) || !\is_readable($path)) {
            $path = $_ENV[self::ENV_CONFIG_PATH]
                ?? $_SERVER[self::ENV_CONFIG_PATH]
                ?? \dirname(__DIR__) . DIRECTORY_SEPARATOR . self::FILENAME;
        }

        if (!\is_readable($path)) {
            $path = \posix_getcwd() . DIRECTORY_SEPARATOR . self::FILENAME;
        }

        if (!\is_readable($path)) {
            $path = $this->getDefaultPath();
        }

        if (!\is_readable($path)) {
            throw new ConfigException('Configuration path could not be determined.');
        }

        return $path;
    }
}
