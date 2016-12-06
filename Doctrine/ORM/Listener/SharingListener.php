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
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Doctrine ORM listener for sharing filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingListener extends AbstractListener
{
    /**
     * @var SharingManagerInterface
     */
    protected $sharingManager;

    /**
     * @var SecurityIdentityManagerInterface
     */
    protected $sidManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

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
     * {@inheritdoc}
     */
    protected function getInitProperties()
    {
        return array(
            'permissionManager' => 'setPermissionManager',
            'sharingManager' => 'setSharingManager',
            'sidManager' => 'setSecurityIdentityManager',
            'tokenStorage' => 'setTokenStorage',
            'dispatcher' => 'setEventDispatcher',
        );
    }
}
