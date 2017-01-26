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

use Sonatra\Component\Security\Event\SetCurrentOrganizationUserEvent;
use Sonatra\Component\Security\Model\OrganizationUserInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SetCurrentOrganizationUserEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        /* @var OrganizationUserInterface $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();

        $event = new SetCurrentOrganizationUserEvent($orgUser);

        $this->assertSame($orgUser, $event->getOrganizationUser());
    }
}
