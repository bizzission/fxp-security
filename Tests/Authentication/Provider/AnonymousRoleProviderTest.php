<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Authentication\Provider;

use Sonatra\Component\Security\Authentication\Provider\AnonymousRoleProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AnonymousRoleProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        /* @var TokenInterface $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $provider = new AnonymousRoleProvider();

        $this->assertSame($token, $provider->authenticate($token));
        $this->assertFalse($provider->supports($token));
    }
}
