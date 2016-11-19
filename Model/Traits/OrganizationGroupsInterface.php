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
use Sonatra\Component\Security\Model\GroupInterface;
use Sonatra\Component\Security\Model\OrganizationInterface;

/**
 * Trait of groups in organization model.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface OrganizationGroupsInterface extends OrganizationInterface
{
    /**
     * Get the groups of organization.
     *
     * @return Collection
     */
    public function getOrganizationGroups();

    /**
     * Get the group names of organization.
     *
     * @return string[]
     */
    public function getOrganizationGroupNames();

    /**
     * Check the presence of group in organization.
     *
     * @param string $group The group name
     *
     * @return bool
     */
    public function hasOrganizationGroup($group);

    /**
     * Add a group in organization.
     *
     * @param GroupInterface $group The group
     *
     * @return self
     */
    public function addOrganizationGroup(GroupInterface $group);

    /**
     * Remove a group in organization.
     *
     * @param GroupInterface $group The group
     *
     * @return self
     */
    public function removeOrganizationGroup(GroupInterface $group);
}
