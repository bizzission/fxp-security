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

use Sonatra\Component\Security\Event\AbstractSecurityEvent;
use Sonatra\Component\Security\IdentityRetrievalEvents;
use Sonatra\Component\Security\ReachableRoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sonatra\Component\Security\Acl\Model\AclManagerInterface;

/**
 * Listener for disable/re-enable the acl doctrine orm filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DisableAclListener implements EventSubscriberInterface
{
    /**
     * @var AclManagerInterface
     */
    protected $aclManager;

    /**
     * Constructor.
     *
     * @param AclManagerInterface $aclManager
     */
    public function __construct(AclManagerInterface $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            IdentityRetrievalEvents::PRE => array('disableAcl', -255),
            ReachableRoleEvents::PRE => array('disableAcl', -255),
            IdentityRetrievalEvents::POST => array('enableAcl', 255),
            ReachableRoleEvents::POST => array('enableAcl', 255),
        );
    }

    /**
     * Disable the acl.
     *
     * @param AbstractSecurityEvent $event The event
     */
    public function disableAcl(AbstractSecurityEvent $event)
    {
        $isEnabled = !$this->aclManager->isDisabled();
        $event->setAclEnabled($isEnabled);

        if ($isEnabled) {
            $this->aclManager->disable();
        }
    }

    /**
     * Enable the acl.
     *
     * @param AbstractSecurityEvent $event The event
     */
    public function enableAcl(AbstractSecurityEvent $event)
    {
        if ($event->isAclEnabled()) {
            $this->aclManager->enable();
        }
    }
}
