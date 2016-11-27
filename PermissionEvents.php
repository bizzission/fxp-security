<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class PermissionEvents
{
    /**
     * The PRE_LOAD event occurs before the loading of the permissions.
     *
     * @Event("Sonatra\Component\Security\Event\PreLoadPermissionsEvent")
     *
     * @var string
     */
    const PRE_LOAD = 'sonatra_security.permission_manager.pre_load';

    /**
     * The POST_LOAD event occurs after the loading of the permissions.
     *
     * @Event("Sonatra\Component\Security\Event\PostLoadPermissionsEvent")
     *
     * @var string
     */
    const POST_LOAD = 'sonatra_security.permission_manager.post_load';

    /**
     * The CHECK_PERMISSION event occurs when the permission is checked.
     * You can override the result with this event.
     *
     * @Event("Sonatra\Component\Security\Event\CheckPermissionEvent")
     *
     * @var string
     */
    const CHECK_PERMISSION = 'sonatra_security.permission_manager.check_permission';
}
