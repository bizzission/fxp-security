<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Acl\Domain;

use Sonatra\Component\Security\Event\AddSecurityIdentityEvent;
use Sonatra\Component\Security\Event\PostSecurityIdentityEvent;
use Sonatra\Component\Security\Event\PreSecurityIdentityEvent;
use Sonatra\Component\Security\IdentityRetrievalEvents;
use Sonatra\Component\Security\Listener\EventStrategyIdentityInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Acl\Domain\SecurityIdentityRetrievalStrategy as BaseSecurityIdentityRetrievalStrategy;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Strategy for retrieving security identities.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SecurityIdentityRetrievalStrategy extends BaseSecurityIdentityRetrievalStrategy
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var array
     */
    private $cacheExec = array();

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface    $dispatcher
     * @param RoleHierarchyInterface      $roleHierarchy
     * @param AuthenticationTrustResolver $authenticationTrustResolver
     */
    public function __construct(EventDispatcherInterface $dispatcher,
                                RoleHierarchyInterface $roleHierarchy,
                                AuthenticationTrustResolver $authenticationTrustResolver)
    {
        $this->dispatcher = $dispatcher;

        parent::__construct($roleHierarchy, $authenticationTrustResolver);
    }

    /**
     * Invalidate the execution cache.
     */
    public function invalidateCache()
    {
        $this->cacheExec = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getSecurityIdentities(TokenInterface $token)
    {
        $id = $this->buildId($token);

        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        $sids = parent::getSecurityIdentities($token);

        // add group security identity
        if ($token instanceof TokenInterface && !$token instanceof AnonymousToken) {
            // dispatch pre event
            $eventPre = new PreSecurityIdentityEvent($token, $sids);
            $this->dispatcher->dispatch(IdentityRetrievalEvents::PRE, $eventPre);
            // dispatch add event
            $eventAdd = new AddSecurityIdentityEvent($token, $sids);
            $this->dispatcher->dispatch(IdentityRetrievalEvents::ADD, $eventAdd);
            $sids = $eventAdd->getSecurityIdentities();
            // dispatch post event
            $eventPost = new PostSecurityIdentityEvent($token, $sids, $eventPre->isAclEnabled());
            $this->dispatcher->dispatch(IdentityRetrievalEvents::POST, $eventPost);

            $this->cacheExec[$id] = $sids;
        }

        return $sids;
    }

    /**
     * Build the unique identifier for execution cache.
     *
     * @param TokenInterface $token The token
     *
     * @return string
     */
    protected function buildId(TokenInterface $token)
    {
        /* @var EventSubscriberInterface[] $listeners */
        $listeners = $this->dispatcher->getListeners(IdentityRetrievalEvents::ADD);
        $id = spl_object_hash($token);

        foreach ($listeners as $listener) {
            if ($listener instanceof EventStrategyIdentityInterface) {
                $id .= '_'.$listener->getCacheId();
            }
        }

        return $id;
    }
}
