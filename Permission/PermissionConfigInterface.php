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

use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Permission config Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface PermissionConfigInterface
{
    /**
     * Get the type. Typically, this is the PHP class name.
     *
     * @return string
     */
    public function getType();

    /**
     * Get the sharing type.
     *
     * @return string
     */
    public function getSharingType();

    /**
     * Get the available fields.
     *
     * @return string[]
     */
    public function getFields();

    /**
     * Get the master relation of permission.
     *
     * @return PropertyPathInterface|string|null
     */
    public function getMaster();
}
