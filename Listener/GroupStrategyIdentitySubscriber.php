<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Listener;

use Sonatra\Component\Security\Event\AddSecurityIdentityEvent;
use Sonatra\Component\Security\Identity\GroupSecurityIdentity;
use Sonatra\Component\Security\IdentityRetrievalEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for add group security identity from token.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GroupStrategyIdentitySubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            IdentityRetrievalEvents::ADD => array('addGroupSecurityIdentities', 0),
        );
    }

    /**
     * Add group security identities.
     *
     * @param AddSecurityIdentityEvent $event The event
     */
    public function addGroupSecurityIdentities(AddSecurityIdentityEvent $event)
    {
        try {
            $sids = $event->getSecurityIdentities();
            $sids = array_merge($sids, GroupSecurityIdentity::fromToken($event->getToken()));
            $event->setSecurityIdentities($sids);
        } catch (\InvalidArgumentException $e) {
            // ignore
        }
    }
}
