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
use Sonatra\Component\Security\Authorization\Expression\IsGrantedProvider;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class IsGrantedProviderTest extends TestCase
{
    public function testIsBasicAuth()
    {
        $object = new \stdClass();
        $authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();

        $authChecker->expects($this->once())
            ->method('isGranted')
            ->with('perm_view', $object)
            ->willReturn(true);

        $expressionLanguage = new ExpressionLanguage(null, array(new IsGrantedProvider()));
        $variables = array(
            'object' => $object,
            'auth_checker' => $authChecker,
        );

        $this->assertTrue($expressionLanguage->evaluate('is_granted("perm_view", object)', $variables));

        $compiled = '$auth_checker && $auth_checker->isGranted("perm_view", $object)';
        $this->assertEquals($compiled, $expressionLanguage->compile('is_granted("perm_view", object)', array('object')));
    }
}
