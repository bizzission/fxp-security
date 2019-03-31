<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Fixtures\Model;

use Fxp\Component\Security\Model\Traits\EditGroupableInterface;
use Fxp\Component\Security\Model\Traits\EditGroupableTrait;
use Fxp\Component\Security\Model\Traits\RoleableInterface;
use Fxp\Component\Security\Model\Traits\RoleableTrait;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MockOrganizationUserRoleableGroupable extends MockOrganizationUser implements RoleableInterface, EditGroupableInterface
{
    use RoleableTrait;
    use EditGroupableTrait;
}
