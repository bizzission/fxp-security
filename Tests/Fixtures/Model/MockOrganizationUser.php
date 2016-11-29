<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Fixtures\Model;

use Sonatra\Component\Security\Model\OrganizationInterface;
use Sonatra\Component\Security\Model\OrganizationUser;
use Sonatra\Component\Security\Model\UserInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MockOrganizationUser extends OrganizationUser
{
    /**
     * Constructor.
     *
     * @param OrganizationInterface $organization The organization
     * @param UserInterface         $user         The user
     * @param int                   $id           The id
     */
    public function __construct(OrganizationInterface $organization, UserInterface $user, $id = 42)
    {
        parent::__construct($organization, $user);

        $this->id = $id;
    }
}
