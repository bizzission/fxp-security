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
final class OrganizationalContextEvents
{
    /**
     * The OrganizationalContextEvents::SET_CURRENT_ORGANIZATION event occurs when the current organization
     * is added in the organizational context.
     *
     * @Event("Sonatra\Component\Security\Event\SetCurrentOrganizationEvent")
     *
     * @var string
     */
    const SET_CURRENT_ORGANIZATION = 'sonatra_security.organizational_event.set_current_organization';

    /**
     * The OrganizationalContextEvents::SET_CURRENT_ORGANIZATION event occurs when the current organization user
     * is added in the organizational context.
     *
     * @Event("Sonatra\Component\Security\Event\SetCurrentOrganizationUserEvent")
     *
     * @var string
     */
    const SET_CURRENT_ORGANIZATION_USER = 'sonatra_security.organizational_event.set_current_organization_user';

    /**
     * The OrganizationalContextEvents::SET_CURRENT_ORGANIZATION event occurs when the optional filter type
     * is changed in the organizational context.
     *
     * @Event("Sonatra\Component\Security\Event\SetOrganizationalOptionalFilterTypeEvent")
     *
     * @var string
     */
    const SET_OPTIONAL_FILTER_TYPE = 'sonatra_security.organizational_event.set_optional_filter_type';
}
