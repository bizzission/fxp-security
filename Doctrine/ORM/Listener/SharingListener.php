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
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Sonatra\Component\Security\Exception\SecurityException;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;

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
     *
     * @param OnFlushEventArgs $args The event
     */
    public function onFlush(OnFlushEventArgs $args)
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
     * Init listener.
     */
    protected function init()
    {
        if (!$this->initialized) {
            $msg = 'The "%s()" method must be called before the init of the doctrine orm sharing listener';

            if (null === $this->permissionManager) {
                throw new SecurityException(sprintf($msg, 'setPermissionManager'));
            }

            $this->initialized = true;
        }
    }
}
