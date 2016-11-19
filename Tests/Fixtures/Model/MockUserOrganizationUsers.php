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

use Sonatra\Component\Security\Model\Traits\OrganizationalOptionalInterface;
use Sonatra\Component\Security\Model\Traits\OrganizationalOptionalTrait;
use Sonatra\Component\Security\Model\Traits\UserOrganizationUsersTrait;
use Sonatra\Component\Security\Model\Traits\UserOrganizationUsersInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MockUserOrganizationUsers extends MockUserRoleable implements OrganizationalOptionalInterface, UserOrganizationUsersInterface
{
    use UserOrganizationUsersTrait;
    use OrganizationalOptionalTrait;
}
