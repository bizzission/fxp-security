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

use Symfony\Component\EventDispatcher\Event;

/**
 * This is a general purpose reachable role event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ReachableRoleEvent extends Event
{
    /**
     * @var \Symfony\Component\Security\Core\Role\RoleInterface[]
     */
    protected $reachableRoles;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Security\Core\Role\RoleInterface[] $reachableRoles The reachable roles
     */
    public function __construct(array $reachableRoles)
    {
        $this->reachableRoles = $reachableRoles;
    }

    /**
     * Set reachable roles.
     *
     * @param \Symfony\Component\Security\Core\Role\RoleInterface[] $reachableRoles
     */
    public function setReachableRoles(array $reachableRoles)
    {
        $this->reachableRoles = $reachableRoles;
    }

    /**
     * Get reachable roles.
     *
     * @return \Symfony\Component\Security\Core\Role\RoleInterface[]
     */
    public function getReachableRoles()
    {
        return $this->reachableRoles;
    }
}
