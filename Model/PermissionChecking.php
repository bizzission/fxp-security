<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Model;

/**
 * Permission checking.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionChecking
{
    /**
     * @var PermissionInterface
     */
    protected $permission;

    /**
     * @var bool
     */
    protected $granted;

    /**
     * Constructor.
     *
     * @param PermissionInterface $permission The permission
     * @param bool                $granted    Check if the permission is granted
     */
    public function __construct(PermissionInterface $permission, $granted)
    {
        $this->permission = $permission;
        $this->granted = $granted;
    }

    /**
     * Get the permission.
     *
     * @return PermissionInterface
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Check if the permission is granted.
     *
     * @return bool
     */
    public function isGranted()
    {
        return $this->granted;
    }
}
