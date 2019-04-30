<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\ObjectFilter;

use Fxp\Component\Security\ObjectFilter\UnitOfWork;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class UnitOfWorkTest extends TestCase
{
    public function testGetObjectIdentifiers(): void
    {
        $uow = new UnitOfWork();

        $this->assertCount(0, $uow->getObjectIdentifiers());
    }

    public function testAttachAndDetach(): void
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        $this->assertCount(0, $uow->getObjectIdentifiers());

        $uow->attach($obj);
        $this->assertCount(1, $uow->getObjectIdentifiers());

        $uow->detach($obj);
        $this->assertCount(0, $uow->getObjectIdentifiers());
    }

    public function testAttachExistingObject(): void
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        $this->assertCount(0, $uow->getObjectIdentifiers());

        $uow->attach($obj);
        $this->assertCount(1, $uow->getObjectIdentifiers());

        $uow->attach($obj);
        $this->assertCount(1, $uow->getObjectIdentifiers());
    }

    public function testDetachNonExistingObject(): void
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        $this->assertCount(0, $uow->getObjectIdentifiers());

        $uow->detach($obj);
        $this->assertCount(0, $uow->getObjectIdentifiers());
    }

    public function testFlush(): void
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

    public function testGetObjectChangeSet(): void
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        $this->assertCount(0, $uow->getObjectIdentifiers());

        $uow->attach($obj);
        $this->assertCount(1, $uow->getObjectIdentifiers());

        $obj->setName('bar');

        $valid = [
            'name' => [
                'old' => 'foo',
                'new' => 'bar',
            ],
        ];

        $this->assertSame($valid, $uow->getObjectChangeSet($obj));
    }

    public function testGetObjectChangeSetWithNonExistingObject(): void
    {
        $uow = new UnitOfWork();
        $obj = new MockObject('foo');

        $this->assertCount(0, $uow->getObjectIdentifiers());
        $this->assertSame([], $uow->getObjectChangeSet($obj));
    }
}
