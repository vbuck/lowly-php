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
 * This interface defines an object which may be serialized for storage.
 *
 * @see \LowlyPHP\Service\Resource\SerializerInterface
 */
interface SerializableInterface
{
    /**
     * Self-serialize the state.
     *
     * @return string
     * @throws
     */
    public function serialize() : string;

    /**
     * Restore from the serialized state.
     *
     * @param string $state
     * @return mixed
     */
    public function restore(string $state);
}
