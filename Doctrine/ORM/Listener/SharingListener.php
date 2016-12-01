<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Doctrine\ORM\Listener;

use Doctrine\ORM\Events;
use Sonatra\Component\Security\Identity\SecurityIdentityManagerInterface;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Sonatra\Component\Security\Exception\SecurityException;
use Doctrine\Common\EventSubscriber;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Doctrine ORM listener for sharing filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingListener implements EventSubscriber
{
    /**
     * @var PermissionManagerInterface|null
     */
    protected $permissionManager;

    /**
     * @var SharingManagerInterface|null
     */
    protected $sharingManager;

    /**
     * @var SecurityIdentityManagerInterface|null
     */
    protected $sidManager;

    /**
     * @var TokenStorageInterface|null
     */
    protected $tokenStorage;

    /**
     * @var EventDispatcherInterface|null
     */
    protected $dispatcher;

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * Specifies the list of listened events.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::onFlush,
        );
    }

    /**
     * On flush action.
     */
    public function onFlush()
    {
        // do nothing, this listener allows to inject the required dependencies in Doctrine ORM Sharing SQL Filter
    }

    /**
     * Set the permission manager.
     *
     * @param PermissionManagerInterface $permissionManager The permission manager
     *
     * @return self
     */
    public function setPermissionManager(PermissionManagerInterface $permissionManager)
    {
        $this->permissionManager = $permissionManager;

        return $this;
    }

    /**
     * Get the Permission Manager.
     *
     * @return PermissionManagerInterface
     */
    public function getPermissionManager()
    {
        $this->init();

        return $this->permissionManager;
    }

    /**
     * Set the sharing manager.
     *
     * @param SharingManagerInterface $sharingManager The sharing manager
     *
     * @return self
     */
    public function setSharingManager(SharingManagerInterface $sharingManager)
    {
        $this->sharingManager = $sharingManager;

        return $this;
    }

    /**
     * Get the sharing manager.
     *
     * @return SharingManagerInterface
     */
    public function getSharingManager()
    {
        $this->init();

        return $this->sharingManager;
    }

    /**
     * Set the security identity manager.
     *
     * @param SecurityIdentityManagerInterface $sidManager The security identity manager
     *
     * @return self
     */
    public function setSecurityIdentityManager(SecurityIdentityManagerInterface $sidManager)
    {
        $this->sidManager = $sidManager;

        return $this;
    }

    /**
     * Get the security identity manager.
     *
     * @return SecurityIdentityManagerInterface
     */
    public function getSecurityIdentityManager()
    {
        $this->init();

        return $this->sidManager;
    }

    /**
     * Set the token storage.
     *
     * @param TokenStorageInterface $tokenStorage The token storage
     *
     * @return self
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    /**
     * Get the token storage.
     *
     * @return TokenStorageInterface
     */
    public function getTokenStorage()
    {
        $this->init();

        return $this->tokenStorage;
    }

    /**
     * Set the event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher
     *
     * @return self
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Get the event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        $this->init();

        return $this->dispatcher;
    }

    /**
     * Init listener.
     */
    protected function init()
    {
        if (!$this->initialized) {
            $msg = 'The "%s()" method must be called before the init of the doctrine orm sharing listener';

            if (null === $this->permissionManager) {
                throw new SecurityException(sprintf($msg, 'setPermissionManager'));
            } elseif (null === $this->sharingManager) {
                throw new SecurityException(sprintf($msg, 'setSharingManager'));
            } elseif (null === $this->sidManager) {
                throw new SecurityException(sprintf($msg, 'setSecurityIdentityManager'));
            } elseif (null === $this->tokenStorage) {
                throw new SecurityException(sprintf($msg, 'setTokenStorage'));
            } elseif (null === $this->dispatcher) {
                throw new SecurityException(sprintf($msg, 'setEventDispatcher'));
            }

            $this->initialized = true;
        }
    }
}
