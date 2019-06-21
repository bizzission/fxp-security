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

use Fxp\Component\Security\Event\ObjectFieldViewGrantedEvent;
use Fxp\Component\Security\Permission\FieldVote;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ObjectFieldViewGrantedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $object = new MockObject('foo');
        $fieldVote = new FieldVote($object, 'name');

        $event = new ObjectFieldViewGrantedEvent($fieldVote);

        static::assertSame($fieldVote, $event->getFieldVote());
        static::assertSame($fieldVote->getSubject()->getObject(), $event->getObject());
        static::assertFalse($event->isSkipAuthorizationChecker());
        static::assertTrue($event->isGranted());

        $event->setGranted(false);
        static::assertTrue($event->isSkipAuthorizationChecker());
        static::assertFalse($event->isGranted());
    }

    public function testEventWithInvalidFieldVote(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "object", "NULL" given');

        $object = MockObject::class;
        $fieldVote = new FieldVote($object, 'foo');

        new ObjectFieldViewGrantedEvent($fieldVote);
    }
}
