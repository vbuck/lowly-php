<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception' . DIRECTORY_SEPARATOR . 'ConfigException.php';

use LowlyPHP\Exception\ConfigException;

/**
 * Application entry-point. Responsible for managing object instances and dependency injection.
 */
class ApplicationManager
{
    /** @var array */
    public static $autoloadPaths = [
        __NAMESPACE__ => __DIR__,
    ];

    /** @var ApplicationManager */
    private static $instance;

    /** @var boolean|null */
    private static $isAutoloadRegistered = null;

    /** @var array */
    protected $config = null;

    /** @var array */
    protected $objects = [];

    /**
     * Load the given class file.
     *
     * @param string $class
     * @throws \Exception
     */
    public static function autoload(string $class) : void
    {
        if (!static::isAutoloadRegistered()) {
            return;
        }

        $class = \ltrim($class, '\\');

        if (\class_exists($class, false)) {
            return;
        }

        $path = static::tryPath($class);

        if (!\is_readable($path)) {
            throw new \Exception(\sprintf('Failed to load "%s" at path.', $class));
        }

        include_once $path;
    }

    /**
     * Search configuration for a value at the given path. Paths are expressed using dot-notation.
     *
     * @param string $path
     * @param array|null $context
     * @return mixed|string
     * @throws ConfigException
     */
    public function config(string $path, array $context = null)
    {
        $this->load();

        if ($context === null) {
            $context = $this->config;
        }

        $components = explode('.', $path);

        while (!empty($components)) {
            $component = \array_shift($components);

            if (isset($context[$component])) {
                return (!empty($components) && \is_array($context[$component]))
                    ? $this->config(\implode('.', $components), $context[$component])
                    : $context[$component];
            }
        }

        return '';
    }

    /**
     * Create an instance of the given type.
     *
     * @param string $type
     * @param array $arguments
     * @throws ConfigException
     * @return mixed
     */
    public function createObject(string $type, array $arguments = [])
    {
        $class = $this->getTypeClass($type);;

        try {
            $this->autoload($class);
            $arguments = $this->prepareArguments($class, $arguments);
        } catch (\ReflectionException $error) {
            throw new ConfigException(sprintf('Failed to prepare arguments for "%s" instance.', $type));
        } catch (\Exception $error) {
            throw new ConfigException(
                sprintf('Failed to prepare "%s" instance: %s', $type, $error->getMessage())
            );
        }

        return new $class(...$arguments);
    }

    /**
     * Get the application base path.
     *
     * @return string
     */
    public function getBasePath() : string
    {
        return dirname(__DIR__);
    }

    /**
     * Get the application manager instance.
     *
     * @return ApplicationManager
     */
    public static function getInstance() : ApplicationManager
    {
        if (!static::$instance) {
            static::$instance = new ApplicationManager();
        }

        return static::$instance;
    }

    /**
     * Get an instance of the given type.
     *
     * @param string $type
     * @param array $arguments
     * @return mixed
     * @throws ConfigException
     */
    public function getObject(string $type, array $arguments = [])
    {
        $key = $type . \sha1(\serialize($arguments));

        if (isset($this->objects[$key])) {
            return $this->objects[$key];
        }

        $this->objects[$key] = $this->createObject($type, $arguments);

        return $this->objects[$key];
    }

    /**
     * Register a path for autoload resolution.
     *
     * @param string $namespace A PSR-4 compatible class namespace.
     * @param string $path The path (absolute or relative) to bind to the namespace.
     * @throws \InvalidArgumentException
     */
    public static function registerAutoloadPath(string $namespace, string $path) : void
    {
        $realpath = \realpath(\dirname(__DIR__) . DIRECTORY_SEPARATOR . $path) ?: $path;

        if (!\is_dir($realpath)) {
            throw new \InvalidArgumentException(
                \sprintf('Invalid path given for autoload registration: %s', $realpath)
            );
        }

        if (!\in_array($realpath, static::$autoloadPaths)) {
            static::$autoloadPaths[$namespace] = $realpath;
        }
    }

    /**
     * Register a class preference dynamically.
     *
     * @param string $type
     * @param string $preference
     * @throws ConfigException
     */
    public function setPreference(string $type, string $preference) : void
    {
        $this->load();
        $this->config['providers'][$type]['type'] = $preference;
    }

    /**
     * Get the configured class for the given type.
     *
     * @param string $type
     * @return string
     * @throws ConfigException
     */
    protected function getTypeClass(string $type) : string
    {
        $this->load();

        if (isset($this->config['providers'][$type]['type'])) {
            return $this->config['providers'][$type]['type'];
        }

        return $type;
    }

    /**
     * Load environment configuration from known paths.
     *
     * @throws ConfigException
     */
    protected function load() : void
    {
        if (!empty($this->config)) {
            return;
        }

        /** @var ConfigResolver $resolver */
        $resolver = new ConfigResolver();
        $merger = new ConfigMerger();

        $config = \array_unique(
            [
                $default = $resolver->getDefaultPath(),
                $path = $resolver->resolve(),
            ]
        );

        $this->config = $merger->merge(...$config);

        foreach ((array) $this->config('paths') as $namespace => $path) {
            ApplicationManager::registerAutoloadPath($namespace, $path);
        }
    }

    /**
     * Arrange arguments for object instantiation.
     *
     * @param string $class
     * @param array $arguments
     * @return array
     * @throws \ReflectionException
     * @throws \Exception
     */
    protected function prepareArguments(string $class, array $arguments = []) : array
    {
        if (!\class_exists($class)) {
            return $arguments;
        }

        if (!\method_exists($class, '__construct')) {
            return $arguments;
        }

        $method = new \ReflectionMethod($class, '__construct');
        $output = [];

        /** @var \ReflectionParameter $parameter */
        foreach ($method->getParameters() as $parameter) {
            if (isset($arguments[$parameter->getName()])) {
                $output[] = $arguments[$parameter->getName()];
            } else {
                $value = $parameter->getClass()
                    ? $parameter->getClass()->getName()
                    : (
                    $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null
                    );

                try {
                    if ($parameter->getClass() && \is_string($value)) {
                        $value = $this->getObject($value);
                    }
                } catch (\Exception $error) {
                    $value = null;
                }

                $output[] = $value;
            }
        }

        return $output;
    }

    /**
     * Determine whether autoload has been registered with SPL.
     *
     * @return bool
     */
    private static function isAutoloadRegistered() : bool
    {
        if (static::$isAutoloadRegistered !== null) {
            return static::$isAutoloadRegistered;
        }

        foreach (\spl_autoload_functions() as $function) {
            if ($function[0] === static::class && $function[1] === 'autoload') {
                return static::$isAutoloadRegistered = true;
            }
        }

        return self::$isAutoloadRegistered = false;
    }

    /**
     * Resolve the given class name to a file path.
     *
     * @param string $class Returns empty string if not found.
     * @return string
     */
    private static function tryPath(string $class) : string
    {
        foreach (static::$autoloadPaths as $namespace => $path) {
            $matches = [];
            $pattern = \sprintf(
                "/%s/",
                \str_replace(
                    '*',
                    '([^\\\]+)',
                    \str_replace(
                        '\\',
                        '\\\\',
                        $namespace
                    )
                )
            );
            \preg_match($pattern, $class, $matches);

            if (empty($matches)) {
                continue;
            }

            $matchedNamespace = \array_shift($matches);
            $wildcardPath = \array_shift($matches);
            $namespacedPath = !empty($wildcardPath)
                ? \str_replace($wildcardPath, '', $matchedNamespace)
                : $matchedNamespace;

            $tryPath = $path . DIRECTORY_SEPARATOR
                . \str_replace(
                    '\\',
                    '/',
                    \str_replace(
                        \rtrim($namespacedPath, '\\') . '\\',
                        '',
                        $class . '.php'
                    )
                );

            if (\file_exists($tryPath)) {
                return $tryPath;
            }
        }

        return '';
    }
}
