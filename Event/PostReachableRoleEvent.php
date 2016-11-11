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
     * @param \Symfony\Component\Security\Core\Role\RoleInterface[] $reachableRoles The reachable roles
     * @param bool                                                  $aclEnabled     Check if the acl is enabled
     */
    public function __construct(array $reachableRoles, $aclEnabled = true)
    {
        $this->reachableRoles = $reachableRoles;
        $this->aclEnabled = $aclEnabled;
    }
}
