<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Permission;

use Sonatra\Component\Security\Identity\SubjectIdentityInterface;
use Sonatra\Component\Security\Model\PermissionInterface;
use Sonatra\Component\Security\Model\SharingInterface;

/**
 * Permission provider Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface PermissionProviderInterface
{
    /**
     * Get all permissions of roles.
     *
     * @param string[] $roles The roles
     *
     * @return PermissionInterface[]
     */
    public function getPermissions(array $roles);

    /**
     * Get all permissions of subjects.
     *
     * @param SubjectIdentityInterface[] $subjects The subjects
     *
     * @return SharingInterface[]
     */
    public function getSharingEntries(array $subjects);
}
