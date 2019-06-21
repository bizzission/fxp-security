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

use Fxp\Component\Security\Event\ObjectViewGrantedEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ObjectViewGrantedEventTest extends TestCase
{
    public function testEvent(): void
    {
        $object = new \stdClass();
        $event = new ObjectViewGrantedEvent($object);

        static::assertSame($object, $event->getObject());
        static::assertFalse($event->isSkipAuthorizationChecker());
        static::assertTrue($event->isGranted());

        $event->setGranted(false);
        static::assertTrue($event->isSkipAuthorizationChecker());
        static::assertFalse($event->isGranted());
    }
}
