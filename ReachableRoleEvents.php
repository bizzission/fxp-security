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
final class ReachableRoleEvents
{
    /**
     * The ReachableRoleEvents::PRE event occurs before the research of all
     * children roles.
     *
     * @Event("Sonatra\Component\Security\Event\PreReachableRoleEvent")
     *
     * @var string
     */
    const PRE = 'sonatra_security.reachable_roles.pre';

    /**
     * The ReachableRoleEvents::POST event occurs after the research of all
     * children roles.
     *
     * @Event("Sonatra\Component\Security\Event\PostReachableRoleEvent")
     *
     * @var string
     */
    const POST = 'sonatra_security.reachable_roles.post';
}
