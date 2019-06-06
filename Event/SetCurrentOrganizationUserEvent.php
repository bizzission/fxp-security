<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Event;

use Fxp\Component\Security\Model\OrganizationUserInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The event of set current organization user by the organizational context.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SetCurrentOrganizationUserEvent extends Event
{
    /**
     * @var null|false|OrganizationUserInterface
     */
    protected $organizationUser;

    /**
     * Constructor.
     *
     * @param null|OrganizationUserInterface $organizationUser The current organization user
     */
    public function __construct($organizationUser)
    {
        $this->organizationUser = $organizationUser;
    }

    /**
     * Get the current organization user.
     *
     * @return null|false|OrganizationUserInterface
     */
    public function getOrganizationUser()
    {
        return $this->organizationUser;
    }
}
