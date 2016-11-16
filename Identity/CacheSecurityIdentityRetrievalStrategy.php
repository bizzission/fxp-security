<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Identity;

use Sonatra\Component\Security\IdentityRetrievalEvents;
use Sonatra\Component\Security\Listener\EventStrategyIdentityInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Strategy for retrieving security identities with execution cache.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CacheSecurityIdentityRetrievalStrategy extends SecurityIdentityRetrievalStrategy
{
    /**
     * @var string|null
     */
    private $cacheIdName;

    /**
     * @var array
     */
    private $cacheExec = array();

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

        return $this->cacheExec[$id] = parent::getSecurityIdentities($token);
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
        $id = spl_object_hash($token);

        if (null === $this->cacheIdName) {
            /* @var EventSubscriberInterface[] $listeners */
            $listeners = $this->dispatcher->getListeners(IdentityRetrievalEvents::ADD);

            foreach ($listeners as $listener) {
                if ($listener instanceof EventStrategyIdentityInterface) {
                    $this->cacheIdName .= '_'.$listener->getCacheId();
                }
            }
        }

        return $id.$this->cacheIdName;
    }
}
