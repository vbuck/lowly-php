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
 * This interface defines a serialization utility for complex data.
 */
interface SerializerInterface
{
    /**
     * Serialize the given input.
     *
     * @param \LowlyPHP\Service\Resource\SerializableInterface|mixed $input
     * @return string
     * @throws \InvalidArgumentException
     */
    public function serialize($input) : string;

    /**
     * Un-serialize the given input.
     *
     * @param string $input
     * @return \LowlyPHP\Service\Resource\SerializableInterface|mixed
     * @throws \Exception
     */
    public function unserialize(string $input);
}
