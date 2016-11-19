<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Model\Traits;

use Doctrine\Common\Collections\Collection;
use Sonatra\Component\Security\Model\OrganizationInterface;
use Sonatra\Component\Security\Model\RoleInterface;

/**
 * Trait of roles in organization model.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface OrganizationRolesInterface extends OrganizationInterface
{
    /**
     * Get the roles of organization.
     *
     * @return Collection
     */
    public function getOrganizationRoles();

    /**
     * Get the role names of organization.
     *
     * @return string[]
     */
    public function getOrganizationRoleNames();

    /**
     * Check the presence of role in organization.
     *
     * @param string $role The role name
     *
     * @return bool
     */
    public function hasOrganizationRole($role);

    /**
     * Add a role in organization.
     *
     * @param RoleInterface $role The role
     *
     * @return self
     */
    public function addOrganizationRole(RoleInterface $role);

    /**
     * Remove a role in organization.
     *
     * @param RoleInterface $role The role
     *
     * @return self
     */
    public function removeOrganizationRole(RoleInterface $role);
}
