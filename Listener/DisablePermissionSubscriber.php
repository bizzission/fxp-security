<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Listener;

use Fxp\Component\Security\Event\AbstractEditableSecurityEvent;
use Fxp\Component\Security\Event\AbstractSecurityEvent;
use Fxp\Component\Security\Event\PostReachableRoleEvent;
use Fxp\Component\Security\Event\PostSecurityIdentityEvent;
use Fxp\Component\Security\Event\PreReachableRoleEvent;
use Fxp\Component\Security\Event\PreSecurityIdentityEvent;
use Fxp\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener for disable/re-enable the permission manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
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
    public static function getSubscribedEvents(): array
    {
        return [
            PreSecurityIdentityEvent::class => ['disablePermissionManager', -255],
            PreReachableRoleEvent::class => ['disablePermissionManager', -255],
            PostSecurityIdentityEvent::class => ['enablePermissionManager', 255],
            PostReachableRoleEvent::class => ['enablePermissionManager', 255],
        ];
    }

    /**
     * Disable the permission manager.
     *
     * @param AbstractEditableSecurityEvent $event The event
     */
    public function disablePermissionManager(AbstractEditableSecurityEvent $event): void
    {
        $event->setPermissionEnabled($this->permManager->isEnabled());
        $this->permManager->setEnabled(false);
    }

    /**
     * Enable the permission manager.
     *
     * @param AbstractSecurityEvent $event The event
     */
    public function enablePermissionManager(AbstractSecurityEvent $event): void
    {
        $this->permManager->setEnabled($event->isPermissionEnabled());
    }
}
