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
use Sonatra\Component\Security\ObjectFilter\ObjectFilterInterface;
use Sonatra\Component\Security\Exception\SecurityException;
use Sonatra\Component\Security\Token\ConsoleToken;
use Sonatra\Component\Security\Exception\AccessDeniedException;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * This class listens to all database activity and automatically adds constraints as permissions.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectFilterListener implements EventSubscriber
{
    /**
     * @var TokenStorageInterface|null
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface|null
     */
    protected $authChecker;

    /**
     * @var PermissionManagerInterface|null
     */
    protected $permissionManager;

    /**
     * @var ObjectFilterInterface|null
     */
    protected $objectFilter;

    /**
     * @var array
     */
    protected $postResetPermissions = array();

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
            Events::postLoad,
            Events::onFlush,
            Events::postFlush,
        );
    }

    /**
     * This method is executed after every load that doctrine performs.
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $token = $this->getTokenStorage()->getToken();

        if (!$this->permissionManager->isEnabled()
                || null === $token || $token instanceof ConsoleToken) {
            return;
        }

        $object = $args->getEntity();
        $this->getObjectFilter()->filter($object);
    }

    /**
     * This method is executed each time doctrine does a flush on an entitymanager.
     *
     * @param OnFlushEventArgs $args
     *
     * @throws AccessDeniedException When insufficient privilege for called action
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $this->postResetPermissions = array();
        $token = $this->getTokenStorage()->getToken();

        if (!$this->permissionManager->isEnabled()
                || null === $token || $token instanceof ConsoleToken) {
            return;
        }

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $this->getObjectFilter()->beginTransaction();

        // check all scheduled insertions
        foreach ($uow->getScheduledEntityInsertions() as $object) {
            $this->postResetPermissions[] = $object;
            $this->getObjectFilter()->restore($object);

            if (!$this->getAuthorizationChecker()->isGranted('create', $object)) {
                throw new AccessDeniedException('Insufficient privilege to create the entity');
            }
        }

        // check all scheduled updates
        foreach ($uow->getScheduledEntityUpdates() as $object) {
            $this->postResetPermissions[] = $object;
            $this->getObjectFilter()->restore($object);

            if (!$this->getAuthorizationChecker()->isGranted('edit', $object)) {
                throw new AccessDeniedException('Insufficient privilege to update the entity');
            }
        }

        // check all scheduled deletations
        foreach ($uow->getScheduledEntityDeletions() as $object) {
            if (!$this->getAuthorizationChecker()->isGranted('delete', $object)) {
                throw new AccessDeniedException('Insufficient privilege to delete the entity');
            }
        }

        $this->getObjectFilter()->commit();
    }

    /**
     * Reset the preloaded permissions used for the insertions.
     */
    public function postFlush()
    {
        $this->getPermissionManager()->resetPreloadPermissions($this->postResetPermissions);
        $this->postResetPermissions = array();
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
     * Set the authorization checker.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker The authorization checker
     *
     * @return self
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authChecker = $authorizationChecker;

        return $this;
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
     * Set the object filter.
     *
     * @param ObjectFilterInterface $objectFilter The object filter
     *
     * @return self
     */
    public function setObjectFilter(ObjectFilterInterface $objectFilter)
    {
        $this->objectFilter = $objectFilter;

        return $this;
    }

    /**
     * Gets security token storage.
     *
     * @return TokenStorageInterface
     */
    protected function getTokenStorage()
    {
        $this->init();

        return $this->tokenStorage;
    }

    /**
     * Gets security authorization checker.
     *
     * @return AuthorizationCheckerInterface
     */
    protected function getAuthorizationChecker()
    {
        $this->init();

        return $this->authChecker;
    }

    /**
     * Get the Permission Manager.
     *
     * @return PermissionManagerInterface
     */
    protected function getPermissionManager()
    {
        $this->init();

        return $this->permissionManager;
    }

    /**
     * Get the Object Filter.
     *
     * @return ObjectFilterInterface
     */
    protected function getObjectFilter()
    {
        $this->init();

        return $this->objectFilter;
    }

    /**
     * Init listener.
     */
    protected function init()
    {
        if (!$this->initialized) {
            $msg = 'The "%s()" method must be called before the init of the doctrine orm object filter listener';

            if (null === $this->tokenStorage) {
                throw new SecurityException(sprintf($msg, 'setTokenStorage'));
            } elseif (null === $this->authChecker) {
                throw new SecurityException(sprintf($msg, 'setAuthorizationChecker'));
            } elseif (null === $this->permissionManager) {
                throw new SecurityException(sprintf($msg, 'setPermissionManager'));
            } elseif (null === $this->objectFilter) {
                throw new SecurityException(sprintf($msg, 'setObjectFilter'));
            }

            $this->initialized = true;
        }
    }
}
