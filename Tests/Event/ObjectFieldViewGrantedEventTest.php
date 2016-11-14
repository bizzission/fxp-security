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

use Sonatra\Component\Security\Core\Authorization\Voter\FieldVote;
use Sonatra\Component\Security\Event\ObjectFieldViewGrantedEvent;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectFieldViewGrantedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $object = new \stdClass();
        $object->foo = 42;
        $fieldVote = new FieldVote($object, 'foo');

        $event = new ObjectFieldViewGrantedEvent($fieldVote);

        $this->assertSame($fieldVote, $event->getFieldVote());
        $this->assertSame($fieldVote->getDomainObject(), $event->getObject());
        $this->assertFalse($event->isSkipAuthorizationChecker());
        $this->assertTrue($event->isGranted());

        $event->setGranted(false);
        $this->assertTrue($event->isSkipAuthorizationChecker());
        $this->assertFalse($event->isGranted());
    }
}
