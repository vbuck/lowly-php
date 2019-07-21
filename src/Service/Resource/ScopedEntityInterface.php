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
 * This interface defines a scoped entity. The scope of an entity refers to its layer data or data view.
 */
interface ScopedEntityInterface extends EntityInterface
{
    const SCOPE_ID = 'scope_id';

    /**
     * Get the scope ID associated with the entity.
     *
     * @return int
     */
    public function getScopeId() : int;

    /**
     * Set the scope ID associated with the entity.
     *
     * @param int $id
     */
    public function setScopeId(int $id) : void;
}
