<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\ObjectFilter;

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Security\ObjectFilter\UnitOfWork;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class UnitOfWorkTest extends TestCase
{
    public function testGetObjectIdentifiers()
    {
        $uow = new UnitOfWork();

        $this->assertCount(0, $uow->getObjectIdentifiers());
    }

    public function testAttachAndDetach()
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        $this->assertCount(0, $uow->getObjectIdentifiers());

        $uow->attach($obj);
        $this->assertCount(1, $uow->getObjectIdentifiers());

        $uow->detach($obj);
        $this->assertCount(0, $uow->getObjectIdentifiers());
    }

    public function testAttachExistingObject()
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        $this->assertCount(0, $uow->getObjectIdentifiers());

        $uow->attach($obj);
        $this->assertCount(1, $uow->getObjectIdentifiers());

        $uow->attach($obj);
        $this->assertCount(1, $uow->getObjectIdentifiers());
    }

    public function testDetachNonExistingObject()
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        $this->assertCount(0, $uow->getObjectIdentifiers());

        $uow->detach($obj);
        $this->assertCount(0, $uow->getObjectIdentifiers());
    }

    public function testFlush()
    {
        $uow = new UnitOfWork();
        $obj1 = new MockObject('foo');
        $obj2 = new MockObject('bar');

        $uow->attach($obj1);
        $uow->attach($obj2);
        $this->assertCount(2, $uow->getObjectIdentifiers());

        $uow->flush();
        $this->assertCount(0, $uow->getObjectIdentifiers());
    }

    public function testGetObjectChangeSet()
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        $this->assertCount(0, $uow->getObjectIdentifiers());

        $uow->attach($obj);
        $this->assertCount(1, $uow->getObjectIdentifiers());

        $obj->setName('bar');

        $valid = array(
            'name' => array(
                'old' => 'foo',
                'new' => 'bar',
            ),
        );

        $this->assertSame($valid, $uow->getObjectChangeSet($obj));
    }

    public function testGetObjectChangeSetWithNonExistingObject()
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        $this->assertCount(0, $uow->getObjectIdentifiers());
        $this->assertSame(array(), $uow->getObjectChangeSet($obj));
    }
}
