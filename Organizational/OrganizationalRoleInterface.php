<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Organizational;

/**
 * Organizational role interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface OrganizationalRoleInterface
{
    /**
     * Check if token has a role.
     *
     * @param string $role The role in organization
     *
     * @return bool
     */
    public function hasRole($role);

    /**
     * Check if token has one of the roles.
     *
     * @param array|string $roles The roles in organization
     *
     * @return bool
     */
    public function hasAnyRole($roles);
}
