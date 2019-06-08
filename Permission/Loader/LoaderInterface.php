<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Permission\Loader;

use Fxp\Component\Security\Permission\PermissionConfigInterface;

/**
 * Permission loader interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Load the permission configurations.
     *
     * @return PermissionConfigInterface[]
     */
    public function loadConfigurations(): array;
}
