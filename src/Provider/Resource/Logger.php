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
use LowlyPHP\Service\Resource\LoggerInterface;
use LowlyPHP\Service\Resource\SerializerInterface;

/**
 * Implementation for {@see LoggerInterface}.
 */
class Logger implements LoggerInterface
{
    const DATE_FORMAT = 'Y-m-d h:i:s';
    const STORAGE_DIR = 'log';

    /** @var bool|resource */
    private $resource;

    /** @var SerializerInterface */
    private $serializer;

    /**
     * @param string $filename
     * @param string|null $path
     * @param ApplicationManager|null $app
     * @param SerializerInterface|null $serializer
     * @throws \LowlyPHP\Exception\ConfigException
     * @throws \Exception
     */
    public function __construct(
        $filename = self::FILE,
        $path = null,
        ApplicationManager $app = null,
        SerializerInterface $serializer = null
    ) {
        $app = $app ?? ApplicationManager::getInstance();

        if (empty($filename)) {
            $filename = self::FILE;
        }

        if (empty($path)) {
            $path = $app->getBasePath() . DIRECTORY_SEPARATOR . self::STORAGE_DIR;
        }

        if (!\is_dir($path) && !@\mkdir($path, 0755, true)) {
            throw new \Exception(\sprintf('Unable to create log path "%s"', $path));
        }

        $this->resource = \fopen($path . DIRECTORY_SEPARATOR . $filename, 'w');
        $this->serializer = $serializer ?? $app->getObject(SerializerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        \fclose($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function critical(\Exception $error) : void
    {
        $this->log(
            $error->getMessage(),
            self::TYPE_CRITICAL,
            $error->getCode(),
            [
                'file' => $error->getFile(),
                'line' => $error->getLine(),
                'trace' => $error->getTraceAsString(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function error(string $message) : void
    {
        $this->log($message, self::TYPE_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    public function log(string $message, string $type = self::TYPE_LOG, int $code = 0, array $data = []) : void
    {
        $data['message'] = $message;
        $data['type'] = $type;
        $data['code'] = $code;

        $output = \sprintf(
            '%s [%s] %s',
            \date(self::DATE_FORMAT),
            $type,
            $this->serializer->serialize($data)
        );

        \fputs($this->resource, $output . PHP_EOL);
    }

    /**
     * {@inheritdoc}
     */
    public function warn(string $message) : void
    {
        $this->log($message, self::TYPE_WARN);
    }
}