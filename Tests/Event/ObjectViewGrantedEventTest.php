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

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Security\Event\ObjectViewGrantedEvent;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectViewGrantedEventTest extends TestCase
{
    public function testEvent()
    {
        $object = new \stdClass();
        $event = new ObjectViewGrantedEvent($object);

        $this->assertSame($object, $event->getObject());
        $this->assertFalse($event->isSkipAuthorizationChecker());
        $this->assertTrue($event->isGranted());

        $event->setGranted(false);
        $this->assertTrue($event->isSkipAuthorizationChecker());
        $this->assertFalse($event->isGranted());
    }
}
