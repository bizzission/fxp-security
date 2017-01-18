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

use Sonatra\Component\Security\Event\AbstractEditableSecurityEvent;
use Sonatra\Component\Security\Event\AbstractSecurityEvent;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Sonatra\Component\Security\ReachableRoleEvents;
use Sonatra\Component\Security\SecurityIdentityEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener for disable/re-enable the permission manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DisablePermissionSubscriber implements EventSubscriberInterface
{
    /**
     * @var PermissionManagerInterface
     */
    protected $permManager;

    /**
     * Constructor.
     *
     * @param PermissionManagerInterface $permManager The permission manager
     */
    public function __construct(PermissionManagerInterface $permManager)
    {
        $this->permManager = $permManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            SecurityIdentityEvents::RETRIEVAL_PRE => array('disablePermissionManager', -255),
            ReachableRoleEvents::PRE => array('disablePermissionManager', -255),
            SecurityIdentityEvents::RETRIEVAL_POST => array('enablePermissionManager', 255),
            ReachableRoleEvents::POST => array('enablePermissionManager', 255),
        );
    }

    /**
     * Disable the permission manager.
     *
     * @param AbstractEditableSecurityEvent $event The event
     */
    public function disablePermissionManager(AbstractEditableSecurityEvent $event)
    {
        $event->setPermissionEnabled($this->permManager->isEnabled());
        $this->permManager->setEnabled(false);
    }

    /**
     * Enable the permission manager.
     *
     * @param AbstractSecurityEvent $event The event
     */
    public function enablePermissionManager(AbstractSecurityEvent $event)
    {
        $this->permManager->setEnabled($event->isPermissionEnabled());
    }
}
