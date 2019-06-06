<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Role;

use Fxp\Component\Security\Model\RoleInterface;

/**
 * Utils for role.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class RoleUtil
{
    /**
     * Format the role names.
     *
     * @param RoleInterface[]|string[] $roles The roles
     *
     * @return string[]
     */
    public static function formatNames(array $roles): array
    {
        return array_map(static function ($role) {
            return static::formatName($role);
        }, $roles);
    }

    /**
     * Format the role name.
     *
     * @param RoleInterface|string $role The role
     *
     * @return string
     */
    public static function formatName($role): string
    {
        return $role instanceof RoleInterface ? $role->getName() : (string) $role;
    }
}
