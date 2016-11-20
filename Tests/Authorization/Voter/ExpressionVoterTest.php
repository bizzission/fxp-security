<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Authorization\Voter;

use Sonatra\Component\Security\Authorization\Voter\ExpressionVoter;
use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityRetrievalStrategyInterface;
use Sonatra\Component\Security\Organizational\OrganizationalContextInterface;
use Sonatra\Component\Security\Organizational\OrganizationalRoleInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ExpressionVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExpressionLanguage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $expressionLanguage;

    /**
     * @var AuthenticationTrustResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $trustResolver;

    /**
     * @var SecurityIdentityRetrievalStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidStrategy;

    /**
     * @var OrganizationalContextInterface
     */
    protected $context;

    /**
     * @var OrganizationalRoleInterface
     */
    protected $orgRole;

    /**
     * @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $token;

    /**
     * @var ExpressionVoter
     */
    protected $voter;

    protected function setUp()
    {
        $this->expressionLanguage = $this->getMockBuilder(ExpressionLanguage::class)->disableOriginalConstructor()->getMock();
        $this->trustResolver = $this->getMockBuilder(AuthenticationTrustResolverInterface::class)->getMock();
        $this->sidStrategy = $this->getMockBuilder(SecurityIdentityRetrievalStrategyInterface::class)->getMock();
        $this->context = $this->getMockBuilder(OrganizationalContextInterface::class)->getMock();
        $this->orgRole = $this->getMockBuilder(OrganizationalRoleInterface::class)->getMock();
        $this->token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->voter = new ExpressionVoter(
            $this->expressionLanguage,
            $this->trustResolver,
            $this->sidStrategy,
            array(
                'organizational_context' => $this->context,
                'organizational_role' => $this->orgRole,
            )
        );
    }

    public function testAddExpressionLanguageProvider()
    {
        /* @var ExpressionFunctionProviderInterface $provider */
        $provider = $this->getMockBuilder(ExpressionFunctionProviderInterface::class)->getMock();

        $this->expressionLanguage->expects($this->once())
            ->method('registerProvider')
            ->with($provider);

        $this->voter->addExpressionLanguageProvider($provider);
    }

    public function testWithoutExpression()
    {
        $res = $this->voter->vote($this->token, null, array(42));

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $res);
    }

    public function getExpressionResults()
    {
        return array(
            array(VoterInterface::ACCESS_GRANTED, true),
            array(VoterInterface::ACCESS_DENIED, false),
        );
    }

    /**
     * @dataProvider getExpressionResults
     *
     * @param int  $resultVoter      The result of voter
     * @param bool $resultExpression The result of expression
     */
    public function testWithExpression($resultVoter, $resultExpression)
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
            new RoleSecurityIdentity(AuthenticatedVoter::IS_AUTHENTICATED_FULLY),
        );

        $this->sidStrategy->expects($this->once())
            ->method('getSecurityIdentities')
            ->with($this->token)
            ->willReturn($sids);

        $this->expressionLanguage->expects($this->once())
            ->method('evaluate')
            ->willReturnCallback(function ($attribute, array $variables) use ($resultExpression) {
                $this->assertInstanceOf(Expression::class, $attribute);
                $this->assertCount(8, $variables);
                $this->assertArrayHasKey('token', $variables);
                $this->assertArrayHasKey('user', $variables);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('subject', $variables);
                $this->assertArrayHasKey('roles', $variables);
                $this->assertArrayHasKey('trust_resolver', $variables);
                $this->assertArrayHasKey('organizational_context', $variables);
                $this->assertArrayHasKey('organizational_role', $variables);
                $this->assertArrayNotHasKey('request', $variables);

                $this->assertEquals(array('ROLE_USER'), $variables['roles']);

                return $resultExpression;
            });

        $expression = new Expression('"ROLE_USER" in roles');
        $res = $this->voter->vote($this->token, null, array($expression));

        $this->assertSame($resultVoter, $res);
    }

    public function testWithoutIdentityStrategyButWithRequestSubject()
    {
        $this->token->expects($this->once())
            ->method('getRoles')
            ->willReturn(array(new Role('ROLE_USER')));

        $this->expressionLanguage->expects($this->once())
            ->method('evaluate')
            ->willReturnCallback(function ($attribute, array $variables) {
                $this->assertInstanceOf(Expression::class, $attribute);
                $this->assertCount(7, $variables);
                $this->assertArrayHasKey('token', $variables);
                $this->assertArrayHasKey('user', $variables);
                $this->assertArrayHasKey('object', $variables);
                $this->assertArrayHasKey('subject', $variables);
                $this->assertArrayHasKey('roles', $variables);
                $this->assertArrayHasKey('trust_resolver', $variables);
                $this->assertArrayNotHasKey('organizational_context', $variables);
                $this->assertArrayNotHasKey('organizational_role', $variables);
                $this->assertArrayHasKey('request', $variables);

                $this->assertEquals(array('ROLE_USER'), $variables['roles']);

                return true;
            });

        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $expression = new Expression('"ROLE_USER" in roles');
        $voter = new ExpressionVoter(
            $this->expressionLanguage,
            $this->trustResolver
        );
        $res = $voter->vote($this->token, $request, array($expression));

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $res);
    }
}
