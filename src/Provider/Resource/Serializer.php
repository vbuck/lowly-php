<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP\Provider\Resource;

use LowlyPHP\Service\Resource\SerializableInterface;
use LowlyPHP\Service\Resource\SerializerInterface;

/**
 * Default serializer implementation for {@see SerializerInterface}.
 */
class Serializer implements SerializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function serialize($input) : string
    {
        if (\is_object($input) && !($input instanceof SerializableInterface)) {
            throw new \InvalidArgumentException('Cannot serialize unsupported input.');
        } elseif ($input instanceof SerializableInterface) {
            return $input->serialize();
        }

        return \json_encode($input);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize(string $input)
    {
        return \json_decode($input, true);
    }
}
