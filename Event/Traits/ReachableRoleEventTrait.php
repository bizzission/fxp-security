<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Event\Traits;

/**
 * This is a general purpose reachable role event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
trait ReachableRoleEventTrait
{
    /**
     * @var \Symfony\Component\Security\Core\Role\Role[]
     */
    protected $reachableRoles;

    /**
     * Set reachable roles.
     *
     * @param \Symfony\Component\Security\Core\Role\Role[] $reachableRoles
     */
    public function setReachableRoles(array $reachableRoles)
    {
        $this->reachableRoles = $reachableRoles;
    }

    /**
     * Get reachable roles.
     *
     * @return \Symfony\Component\Security\Core\Role\Role[]
     */
    public function getReachableRoles()
    {
        return $this->reachableRoles;
    }
}
