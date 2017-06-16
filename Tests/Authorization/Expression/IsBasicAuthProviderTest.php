<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Authorization\Expression;

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Security\Authorization\Expression\IsBasicAuthProvider;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class IsBasicAuthProviderTest extends TestCase
{
    public function testIsBasicAuth()
    {
        $token = $this->getMockBuilder(UsernamePasswordToken::class)->disableOriginalConstructor()->getMock();
        $trustResolver = $this->getMockBuilder(AuthenticationTrustResolverInterface::class)->getMock();

        $trustResolver->expects($this->once())
            ->method('isAnonymous')
            ->with($token)
            ->willReturn(false);

        $expressionLanguage = new ExpressionLanguage(null, array(new IsBasicAuthProvider()));
        $variables = array(
            'token' => $token,
            'trust_resolver' => $trustResolver,
        );

        $this->assertTrue($expressionLanguage->evaluate('is_basic_auth()', $variables));

        $compiled = '$token && $token instanceof \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken && !$trust_resolver->isAnonymous($token)';
        $this->assertEquals($compiled, $expressionLanguage->compile('is_basic_auth()'));
    }
}
