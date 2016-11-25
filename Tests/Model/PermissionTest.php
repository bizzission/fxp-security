<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Model;

use Sonatra\Component\Security\PermissionContexts;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockPermission;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionTest extends \PHPUnit_Framework_TestCase
{
    public function testModel()
    {
        $perm = new MockPermission();
        $perm->setOperation('foo');
        $perm->setClass(MockObject::class);
        $perm->setField('name');
        $perm->setContexts(array(PermissionContexts::ROLE));

        $this->assertNull($perm->getId());
        $this->assertSame('foo', $perm->getOperation());
        $this->assertSame(MockObject::class, $perm->getClass());
        $this->assertSame('name', $perm->getField());
        $this->assertSame(array(PermissionContexts::ROLE), $perm->getContexts());
        $this->assertCount(0, $perm->getRoles());
    }
}
