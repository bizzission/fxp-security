<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Permission;

/**
 * Permission factory interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface PermissionFactoryInterface
{
    /**
     * Create the permission configurations.
     *
     * @return PermissionConfigCollection
     */
    public function createConfigurations(): PermissionConfigCollection;
}
