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
use Sonatra\Component\Security\Model\PermissionInterface;

/**
 * Interface of model with permissions.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface PermissionsInterface
{
    /**
     * Get the permissions.
     *
     * @return Collection|PermissionInterface[]
     */
    public function getPermissions();

    /**
     * Check if the role has the permission.
     *
     * @param PermissionInterface $permission The permission
     *
     * @return bool
     */
    public function hasPermission(PermissionInterface $permission);

    /**
     * Add the permission.
     *
     * @param PermissionInterface $permission The permission
     *
     * @return self
     */
    public function addPermission(PermissionInterface $permission);

    /**
     * Remove the permission.
     *
     * @param PermissionInterface $permission The permission
     *
     * @return self
     */
    public function removePermission(PermissionInterface $permission);
}
