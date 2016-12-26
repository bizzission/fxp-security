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
     * Get the permissions by subject.
     *
     * @param FieldVote|SubjectIdentityInterface|object|string|null $subject The subject instance or classname
     *
     * @return PermissionInterface[]
     */
    public function getPermissionsBySubject($subject = null);

    /**
     * Get the class name of association field.
     *
     * @param PermissionConfigInterface $config The permission config
     *
     * @return string|null
     */
    public function getMasterClass(PermissionConfigInterface $config);
}
