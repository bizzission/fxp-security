<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Sharing;

use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\SubjectIdentityInterface;
use Sonatra\Component\Security\Model\RoleInterface;
use Sonatra\Component\Security\Model\SharingInterface;

/**
 * Sharing provider Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface SharingProviderInterface
{
    /**
     * Set the sharing manager.
     *
     * @param SharingManagerInterface $sharingManager The sharing manager
     *
     * @return self
     */
    public function setSharingManager(SharingManagerInterface $sharingManager);

    /**
     * Get the roles with permissions.
     *
     * @param string[] $roles The roles
     *
     * @return RoleInterface[]
     */
    public function getPermissionRoles(array $roles);

    /**
     * Get all permissions of subjects.
     *
     * @param SubjectIdentityInterface[]       $subjects The subjects
     * @param SecurityIdentityInterface[]|null $sids     The security identities to filter the sharing entries
     *
     * @return SharingInterface[]
     */
    public function getSharingEntries(array $subjects, $sids = null);
}