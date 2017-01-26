<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Model\Traits;

use Sonatra\Component\Security\Tests\Fixtures\Model\MockObjectOwnerable;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockUserRoleable;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OwnerableTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testModel()
    {
        $user = new MockUserRoleable();
        $ownerable = new MockObjectOwnerable('foo');

        $this->assertNull($ownerable->getOwner());
        $this->assertNull($ownerable->getOwnerId());

        $ownerable->setOwner($user);

        $this->assertSame($user, $ownerable->getOwner());
        $this->assertSame(50, $ownerable->getOwnerId());
    }
}
