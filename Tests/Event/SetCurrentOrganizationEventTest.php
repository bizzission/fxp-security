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
use Sonatra\Component\Security\Event\SetCurrentOrganizationEvent;
use Sonatra\Component\Security\Model\OrganizationInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SetCurrentOrganizationEventTest extends TestCase
{
    public function testEvent()
    {
        /* @var OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();

        $event = new SetCurrentOrganizationEvent($org);

        $this->assertSame($org, $event->getOrganization());
    }
}
