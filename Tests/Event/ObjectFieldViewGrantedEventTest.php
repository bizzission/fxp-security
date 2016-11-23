<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Event;

use Sonatra\Component\Security\Event\ObjectFieldViewGrantedEvent;
use Sonatra\Component\Security\Permission\FieldVote;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectFieldViewGrantedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $object = new MockObject('foo');
        $fieldVote = new FieldVote($object, 'name');

        $event = new ObjectFieldViewGrantedEvent($fieldVote);

        $this->assertSame($fieldVote, $event->getFieldVote());
        $this->assertSame($fieldVote->getSubject()->getObject(), $event->getObject());
        $this->assertFalse($event->isSkipAuthorizationChecker());
        $this->assertTrue($event->isGranted());

        $event->setGranted(false);
        $this->assertTrue($event->isSkipAuthorizationChecker());
        $this->assertFalse($event->isGranted());
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "object", "NULL" given
     */
    public function testEventWithInvalidFieldVote()
    {
        $object = MockObject::class;
        $fieldVote = new FieldVote($object, 'foo');

        new ObjectFieldViewGrantedEvent($fieldVote);
    }
}
