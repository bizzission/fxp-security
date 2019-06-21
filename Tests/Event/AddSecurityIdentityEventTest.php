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

use Fxp\Component\Security\Event\AddSecurityIdentityEvent;
use Fxp\Component\Security\Identity\SecurityIdentityInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class AddSecurityIdentityEventTest extends TestCase
{
    public function testEvent(): void
    {
        /** @var TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $sids = [
            $this->getMockBuilder(SecurityIdentityInterface::class)->getMock(),
        ];

        $event = new AddSecurityIdentityEvent($token, $sids);

        static::assertSame($token, $event->getToken());
        static::assertSame($sids, $event->getSecurityIdentities());

        $sids[] = $this->getMockBuilder(SecurityIdentityInterface::class)->getMock();
        $event->setSecurityIdentities($sids);

        static::assertSame($sids, $event->getSecurityIdentities());
    }
}
