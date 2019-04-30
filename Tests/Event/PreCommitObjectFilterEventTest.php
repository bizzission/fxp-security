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

use Fxp\Component\Security\Event\PreCommitObjectFilterEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class PreCommitObjectFilterEventTest extends TestCase
{
    public function testEvent(): void
    {
        $objects = [
            new \stdClass(),
            new \stdClass(),
            new \stdClass(),
        ];

        $event = new PreCommitObjectFilterEvent($objects);
        $this->assertSame($objects, $event->getObjects());
    }
}
