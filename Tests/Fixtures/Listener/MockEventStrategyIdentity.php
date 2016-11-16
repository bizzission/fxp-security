<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Fixtures\Listener;

use Sonatra\Component\Security\IdentityRetrievalEvents;
use Sonatra\Component\Security\Listener\EventStrategyIdentityInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MockEventStrategyIdentity implements EventSubscriberInterface, EventStrategyIdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            IdentityRetrievalEvents::ADD => array('onAddIdentity', 0),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheId()
    {
        return 'cache_id';
    }

    /**
     * Action on add identity.
     */
    public function onAddIdentity()
    {
        // do nothing
    }
}
