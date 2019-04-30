<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Event;

use Fxp\Component\Security\Event\RestoreViewGrantedEvent;
use Fxp\Component\Security\Permission\FieldVote;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 * @coversNothing
 */
final class RestoreViewGrantedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $object = new MockObject('foo');
        $fieldVote = new FieldVote($object, 'name');
        $oldValue = 'bar';
        $newValue = $object->getName();

        $event = new RestoreViewGrantedEvent($fieldVote, $oldValue, $newValue);

        $this->assertSame($fieldVote, $event->getFieldVote());
        $this->assertSame($fieldVote->getSubject()->getObject(), $event->getObject());
        $this->assertSame($oldValue, $event->getOldValue());
        $this->assertSame($newValue, $event->getNewValue());
        $this->assertFalse($event->isSkipAuthorizationChecker());
        $this->assertTrue($event->isGranted());

        $event->setGranted(false);
        $this->assertTrue($event->isSkipAuthorizationChecker());
        $this->assertFalse($event->isGranted());
    }

    public function testEventWithInvalidFieldVote(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "object", "NULL" given');

        $object = \stdClass::class;
        $fieldVote = new FieldVote($object, 'foo');
        $oldValue = 23;
        $newValue = 46;

        new RestoreViewGrantedEvent($fieldVote, $oldValue, $newValue);
    }
}
