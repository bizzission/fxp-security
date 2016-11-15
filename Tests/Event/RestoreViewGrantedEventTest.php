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

use Sonatra\Component\Security\Authorization\Voter\FieldVote;
use Sonatra\Component\Security\Event\RestoreViewGrantedEvent;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RestoreViewGrantedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $object = new \stdClass();
        $object->foo = 42;
        $fieldVote = new FieldVote($object, 'foo');
        $oldValue = 23;
        $newValue = $object->foo;

        $event = new RestoreViewGrantedEvent($fieldVote, $oldValue, $newValue);

        $this->assertSame($fieldVote, $event->getFieldVote());
        $this->assertSame($fieldVote->getDomainObject(), $event->getObject());
        $this->assertSame($oldValue, $event->getOldValue());
        $this->assertSame($newValue, $event->getNewValue());
        $this->assertFalse($event->isSkipAuthorizationChecker());
        $this->assertTrue($event->isGranted());

        $event->setGranted(false);
        $this->assertTrue($event->isSkipAuthorizationChecker());
        $this->assertFalse($event->isGranted());
    }
}
