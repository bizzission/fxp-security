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
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Utils for role.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class RoleUtil
{
    /**
     * Format the roles.
     *
     * @param Role[]|string[] $roles The roles
     * @param bool            $force Force to convert the role name
     *
     * @return Role[]|string[]
     */
    public static function formatRoles(array $roles, $force = false): array
    {
        if ($force || version_compare(Kernel::VERSION, '4.3', '<')) {
            $roles = array_map(static function ($role) {
                return !$role instanceof Role ? new Role((string) $role) : $role;
            }, $roles);
        }

        return $roles;
    }

    /**
     * Format the role.
     *
     * @param Role|string $role  The role
     * @param bool        $force Force to convert the role name
     *
     * @return Role|string
     */
    public static function formatRole($role, $force = false)
    {
        if ($force || version_compare(Kernel::VERSION, '4.3', '<')) {
            $role = !$role instanceof Role ? new Role((string) $role) : $role;
        }

        return $role;
    }

    /**
     * Format the role names.
     *
     * @param Role[]|RoleInterface[]|string[] $roles The roles
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
     * @param Role|RoleInterface|string $role The role
     *
     * @return string
     */
    public static function formatName($role): string
    {
        if ($role instanceof RoleInterface) {
            return $role->getName();
        }

        return $role instanceof Role ? $role->getRole() : (string) $role;
    }
}
