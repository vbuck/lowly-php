<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Service\Resource;

/**
 * This interface defines a generic system log utility.
 */
interface LoggerInterface
{
    const FILE = 'system.log';
    const TYPE_CRITICAL = 'CRITICAL';
    const TYPE_ERROR = 'ERROR';
    const TYPE_LOG = 'LOG';
    const TYPE_WARN = 'WARN';

    /**
     * Log an exception.
     *
     * @param \Exception $error
     */
    public function critical(\Exception $error) : void;

    /**
     * Log an error message.
     *
     * @param string $message
     */
    public function error(string $message) : void;

    /**
     * Log a general message.
     *
     * @param string $message
     * @param string $type
     * @param int $code
     * @param array $data
     */
    public function log(string $message, string $type = self::TYPE_LOG, int $code = 0, array $data = []) : void;

    /**
     * Log a warning message.
     *
     * @param string $message
     */
    public function warn(string $message) : void;
}
