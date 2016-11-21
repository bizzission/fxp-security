<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Authorization\Voter;

use Sonatra\Component\Security\Event\GetExpressionVariablesEvent;
use Sonatra\Component\Security\ExpressionVoterEvents;
use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\SecurityIdentityRetrievalStrategyInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Override the Expression Voter to use Security Identity Retrieval Strategy to get all roles.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ExpressionVoter implements VoterInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var AuthenticationTrustResolverInterface
     */
    private $trustResolver;

    /**
     * @var SecurityIdentityRetrievalStrategyInterface|null
     */
    private $sidStrategy;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface                        $dispatcher         The event dispatcher
     * @param ExpressionLanguage                              $expressionLanguage The expression language
     * @param AuthenticationTrustResolverInterface            $trustResolver      The trust resolver
     * @param SecurityIdentityRetrievalStrategyInterface|null $sidStrategy        The security identity retrieval strategy
     */
    public function __construct(EventDispatcherInterface $dispatcher,
                                ExpressionLanguage $expressionLanguage,
                                AuthenticationTrustResolverInterface $trustResolver,
                                SecurityIdentityRetrievalStrategyInterface $sidStrategy = null)
    {
        $this->dispatcher = $dispatcher;
        $this->expressionLanguage = $expressionLanguage;
        $this->trustResolver = $trustResolver;
        $this->sidStrategy = $sidStrategy;
    }

    /**
     * Add the expression function provider.
     *
     * @param ExpressionFunctionProviderInterface $provider The expression function provider
     */
    public function addExpressionLanguageProvider(ExpressionFunctionProviderInterface $provider)
    {
        $this->expressionLanguage->registerProvider($provider);
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;
        $variables = null;

        foreach ($attributes as $attribute) {
            if (!$attribute instanceof Expression) {
                continue;
            }

            if (null === $variables) {
                $variables = $this->getVariables($token, $subject);
            }

            $result = VoterInterface::ACCESS_DENIED;

            if ($this->expressionLanguage->evaluate($attribute, $variables)) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return $result;
    }

    /**
     * Get the variables.
     *
     * @param TokenInterface $token   The token
     * @param mixed          $subject The subject to secure
     *
     * @return array
     */
    protected function getVariables(TokenInterface $token, $subject)
    {
        $event = new GetExpressionVariablesEvent();
        $this->dispatcher->dispatch(ExpressionVoterEvents::GET_VARIABLES, $event);

        $variables = array_merge($event->getVariables(), array(
            'token' => $token,
            'user' => $token->getUser(),
            'object' => $subject,
            'subject' => $subject,
            'roles' => $this->getAllRoles($token),
            'trust_resolver' => $this->trustResolver,
        ));

        if ($subject instanceof Request) {
            $variables['request'] = $subject;
        }

        return $variables;
    }

    /**
     * Get all roles.
     *
     * @param TokenInterface $token The token
     *
     * @return string[]
     */
    private function getAllRoles(TokenInterface $token)
    {
        if (null !== $this->sidStrategy) {
            $sids = $this->sidStrategy->getSecurityIdentities($token);

            return $this->filterRolesIdentities($sids);
        }

        return array_map(function (RoleInterface $role) {
            return $role->getRole();
        }, $token->getRoles());
    }

    /**
     * Filter the role identities and convert to role instances.
     *
     * @param SecurityIdentityInterface[] $sids The security identities
     *
     * @return string[]
     */
    private function filterRolesIdentities(array $sids)
    {
        $roles = array();

        foreach ($sids as $sid) {
            if ($sid instanceof RoleSecurityIdentity && false === strpos($sid->getIdentifier(), 'IS_')) {
                $roles[] = $sid->getIdentifier();
            }
        }

        return $roles;
    }
}
