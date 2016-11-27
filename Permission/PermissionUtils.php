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

/**
 * Permission utils.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class PermissionUtils
{
    /**
     * Get the action for the map of permissions.
     *
     * @param string|null $action The action
     *
     * @return string
     */
    public static function getMapAction($action = null)
    {
        return null !== $action
            ? $action
            : '_global';
    }
}
