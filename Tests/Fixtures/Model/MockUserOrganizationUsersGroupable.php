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

use Fxp\Component\Security\Model\Traits\GroupableInterface;
use Fxp\Component\Security\Model\Traits\GroupableTrait;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MockUserOrganizationUsersGroupable extends MockUserOrganizationUsers implements GroupableInterface
{
    use GroupableTrait;
}
