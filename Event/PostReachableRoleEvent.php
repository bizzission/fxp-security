<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Event;

use Sonatra\Component\Security\Event\Traits\ReachableRoleEventTrait;

/**
 * The post reachable role event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PostReachableRoleEvent extends AbstractSecurityEvent
{
    use ReachableRoleEventTrait;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Security\Core\Role\Role[] $reachableRoles    The reachable roles
     * @param bool                                         $permissionEnabled Check if the permission manager is enabled
     */
    public function __construct(array $reachableRoles, $permissionEnabled = true)
    {
        $this->reachableRoles = $reachableRoles;
        $this->permissionEnabled = $permissionEnabled;
    }
}
