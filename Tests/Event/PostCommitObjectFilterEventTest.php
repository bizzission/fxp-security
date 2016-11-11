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

use Sonatra\Component\Security\Event\PostCommitObjectFilterEvent;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PostCommitObjectFilterEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $objects = array(
            new \stdClass(),
            new \stdClass(),
            new \stdClass(),
        );

        $event = new PostCommitObjectFilterEvent($objects);
        $this->assertSame($objects, $event->getObjects());
    }
}
