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
final class AclManipulatorEvents
{
    /**
     * The AclManipulatorEvents::GET event occurs before the getting of acls.
     *
     * @Event("Sonatra\Component\Security\Event\AclManipulatorEvent")
     *
     * @var string
     */
    const GET = 'sonatra_security.acl_manipulator.get';

    /**
     * The AclManipulatorEvents::ADD event occurs before the adding of acls.
     *
     * @Event("Sonatra\Component\Security\Event\AclManipulatorEvent")
     *
     * @var string
     */
    const ADD = 'sonatra_security.acl_manipulator.add';

    /**
     * The AclManipulatorEvents::SET event occurs before the setting of acls.
     *
     * @Event("Sonatra\Component\Security\Event\AclManipulatorEvent")
     *
     * @var string
     */
    const SET = 'sonatra_security.acl_manipulator.set';

    /**
     * The AclManipulatorEvents::REVOKE event occurs before the revoking of acls.
     *
     * @Event("Sonatra\Component\Security\Event\AclManipulatorEvent")
     *
     * @var string
     */
    const REVOKE = 'sonatra_security.acl_manipulator.revoke';

    /**
     * The AclManipulatorEvents::DELETE event occurs before the deleting of acls.
     *
     * @Event("Sonatra\Component\Security\Event\AclManipulatorEvent")
     *
     * @var string
     */
    const DELETE = 'sonatra_security.acl_manipulator.delete';
}
