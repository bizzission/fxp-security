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

use Fxp\Component\Security\Event\SetCurrentOrganizationUserEvent;
use Fxp\Component\Security\Model\OrganizationUserInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SetCurrentOrganizationUserEventTest extends TestCase
{
    public function testEvent(): void
    {
        /** @var OrganizationUserInterface $orgUser */
        $orgUser = $this->getMockBuilder(OrganizationUserInterface::class)->getMock();

        $event = new SetCurrentOrganizationUserEvent($orgUser);

        static::assertSame($orgUser, $event->getOrganizationUser());
    }
}
