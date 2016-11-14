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
use Sonatra\Component\Security\IdentityRetrievalEvents;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Sonatra\Component\Security\ReachableRoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener for disable/re-enable the permission manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DisablePermissionListener implements EventSubscriberInterface
{
    /**
     * @var PermissionManagerInterface
     */
    protected $permManager;

    /**
     * Constructor.
     *
     * @param PermissionManagerInterface $permManager
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
            IdentityRetrievalEvents::PRE => array('disablePermissionManager', -255),
            ReachableRoleEvents::PRE => array('disablePermissionManager', -255),
            IdentityRetrievalEvents::POST => array('enablePermissionManager', 255),
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
        $isEnabled = !$this->permManager->isDisabled();
        $event->setPermissionEnabled($isEnabled);

        if ($isEnabled) {
            $this->permManager->disable();
        }
    }

    /**
     * Enable the permission manager.
     *
     * @param AbstractSecurityEvent $event The event
     */
    public function enablePermissionManager(AbstractSecurityEvent $event)
    {
        if ($event->isPermissionEnabled()) {
            $this->permManager->enable();
        }
    }
}
