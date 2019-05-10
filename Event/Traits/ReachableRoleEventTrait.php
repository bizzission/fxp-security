<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Event\Traits;

/**
 * This is a general purpose reachable role event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
trait ReachableRoleEventTrait
{
    /**
     * @var string[]
     */
    protected $reachableRoles = [];

    /**
     * Set reachable roles.
     *
     * @param string[] $reachableRoles
     */
    public function setReachableRoles(array $reachableRoles): void
    {
        $this->reachableRoles = $reachableRoles;
    }

    /**
     * Get reachable roles.
     *
     * @return string[]
     */
    public function getReachableRoles(): array
    {
        return $this->reachableRoles;
    }
}
