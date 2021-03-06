<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Doctrine\ORM\Listener;

use Doctrine\Common\EventSubscriber;
use Fxp\Component\Security\Exception\SecurityException;
use Fxp\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Abstract doctrine listener class.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractListener implements EventSubscriber
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var PermissionManagerInterface
     */
    protected $permissionManager;

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * Set the token storage.
     *
     * @param TokenStorageInterface $tokenStorage The token storage
     *
     * @return static
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage): self
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    /**
     * Gets security token storage.
     *
     * @throws
     *
     * @return TokenStorageInterface
     */
    public function getTokenStorage(): TokenStorageInterface
    {
        $this->init();

        return $this->tokenStorage;
    }

    /**
     * Set the permission manager.
     *
     * @param PermissionManagerInterface $permissionManager The permission manager
     *
     * @return static
     */
    public function setPermissionManager(PermissionManagerInterface $permissionManager): self
    {
        $this->permissionManager = $permissionManager;

        return $this;
    }

    /**
     * Get the Permission Manager.
     *
     * @throws
     *
     * @return PermissionManagerInterface
     */
    public function getPermissionManager(): PermissionManagerInterface
    {
        $this->init();

        return $this->permissionManager;
    }

    /**
     * Init listener.
     *
     * @throws SecurityException
     */
    protected function init(): void
    {
        if (!$this->initialized) {
            $msg = 'The "%s()" method must be called before the init of the "%s" class';

            foreach ($this->getInitProperties() as $property => $setterMethod) {
                if (null === $this->{$property}) {
                    throw new SecurityException(sprintf($msg, $setterMethod, \get_class($this)));
                }
            }

            $this->initialized = true;
        }
    }

    /**
     * Get the map of properties and methods required on the init.
     *
     * @return array
     */
    abstract protected function getInitProperties(): array;
}
