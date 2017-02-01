<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Permission;

use Sonatra\Component\Security\Event\CheckPermissionEvent;
use Sonatra\Component\Security\Event\PostLoadPermissionsEvent;
use Sonatra\Component\Security\Event\PreLoadPermissionsEvent;
use Sonatra\Component\Security\Identity\IdentityUtils;
use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\SubjectIdentity;
use Sonatra\Component\Security\Identity\SubjectIdentityInterface;
use Sonatra\Component\Security\Identity\SubjectUtils;
use Sonatra\Component\Security\Model\PermissionChecking;
use Sonatra\Component\Security\Model\RoleInterface;
use Sonatra\Component\Security\PermissionEvents;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Permission manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionManager extends AbstractPermissionManager
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var PermissionProviderInterface
     */
    protected $provider;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface     $dispatcher       The event dispatcher
     * @param PermissionProviderInterface  $provider         The permission provider
     * @param PropertyAccessorInterface    $propertyAccessor The property accessor
     * @param SharingManagerInterface|null $sharingManager   The sharing manager
     * @param PermissionConfigInterface[]  $configs          The permission configs
     */
    public function __construct(EventDispatcherInterface $dispatcher,
                                PermissionProviderInterface $provider,
                                PropertyAccessorInterface $propertyAccessor,
                                SharingManagerInterface $sharingManager = null,
                                array $configs = array())
    {
        parent::__construct($sharingManager, $configs);

        $this->dispatcher = $dispatcher;
        $this->provider = $provider;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMaster($subject)
    {
        if (null !== $subject) {
            $subject = SubjectUtils::getSubjectIdentity($subject);

            if ($this->hasConfig($subject->getType())) {
                $config = $this->getConfig($subject->getType());

                if (null !== $config->getMaster()) {
                    if (is_object($subject->getObject())) {
                        $value = $this->propertyAccessor->getValue($subject->getObject(), $config->getMaster());

                        if (is_object($value)) {
                            $subject = SubjectIdentity::fromObject($value);
                        }
                    } else {
                        $subject = SubjectIdentity::fromClassname($this->provider->getMasterClass($config));
                    }
                }
            }
        }

        return $subject;
    }

    /**
     * {@inheritdoc}
     */
    protected function doIsManaged(SubjectIdentityInterface $subject, $field = null)
    {
        if ($this->hasConfig($subject->getType())) {
            if (null === $field) {
                return true;
            } else {
                $config = $this->getConfig($subject->getType());

                return $config->hasField($field);
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doIsGranted(array $sids, array $permissions, $subject = null, $field = null)
    {
        if (null !== $subject) {
            $this->preloadPermissions(array($subject));
            $this->preloadSharingRolePermissions(array($subject));
        }

        $id = $this->loadPermissions($sids);

        foreach ($permissions as $operation) {
            if (!$this->doIsGrantedPermission($id, $sids, $operation, $subject, $field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetRolePermissions(RoleInterface $role, $subject = null)
    {
        $permissions = array();
        $sid = new RoleSecurityIdentity($role->getRole());

        foreach ($this->provider->getPermissionsBySubject($subject) as $permission) {
            $permissions[] = new PermissionChecking(
                $permission,
                $this->isGranted(array($sid), array($permission->getOperation()), $subject)
            );
        }

        return $permissions;
    }

    /**
     * Action to determine whether access is granted for a specific operation.
     *
     * @param string                        $id        The cache id
     * @param SecurityIdentityInterface[]   $sids      The security identities
     * @param string                        $operation The operation
     * @param SubjectIdentityInterface|null $subject   The subject
     * @param string|null                   $field     The field of subject
     *
     * @return bool
     */
    private function doIsGrantedPermission($id, array $sids, $operation, $subject = null, $field = null)
    {
        $event = new CheckPermissionEvent($sids, $this->cache[$id], $operation, $subject, $field);
        $this->dispatcher->dispatch(PermissionEvents::CHECK_PERMISSION, $event);

        if (is_bool($event->isGranted())) {
            return $event->isGranted();
        }

        $classAction = PermissionUtils::getMapAction(null !== $subject ? $subject->getType() : null);
        $fieldAction = PermissionUtils::getMapAction($field);

        return isset($this->cache[$id][$classAction][$fieldAction][$operation])
            || $this->isSharingGranted($operation, $subject, $field);
    }

    /**
     * Load the permissions of sharing roles.
     *
     * @param SubjectIdentityInterface[] $subjects The subjects
     */
    private function preloadSharingRolePermissions(array $subjects)
    {
        if (null !== $this->sharingManager) {
            $this->sharingManager->preloadRolePermissions($subjects);
        }
    }

    /**
     * Load the permissions of roles and returns the id of cache.
     *
     * @param SecurityIdentityInterface[] $sids The security identities
     *
     * @return string
     */
    private function loadPermissions(array $sids)
    {
        $roles = IdentityUtils::filterRolesIdentities($sids);
        $id = implode('|', $roles);

        if (!array_key_exists($id, $this->cache)) {
            $this->cache[$id] = array();
            $preEvent = new PreLoadPermissionsEvent($sids, $roles);
            $this->dispatcher->dispatch(PermissionEvents::PRE_LOAD, $preEvent);
            $perms = $this->provider->getPermissions($roles);

            $this->buildSystemPermissions($id);

            foreach ($perms as $perm) {
                $class = PermissionUtils::getMapAction($perm->getClass());
                $field = PermissionUtils::getMapAction($perm->getField());
                $this->cache[$id][$class][$field][$perm->getOperation()] = true;
            }

            $postEvent = new PostLoadPermissionsEvent($sids, $roles, $this->cache[$id]);
            $this->dispatcher->dispatch(PermissionEvents::POST_LOAD, $postEvent);
            $this->cache[$id] = $postEvent->getPermissionMap();
        }

        return $id;
    }

    /**
     * Build the system permissions.
     *
     * @param string $id The cache id
     */
    private function buildSystemPermissions($id)
    {
        foreach ($this->configs as $config) {
            foreach ($config->getOperations() as $operation) {
                $field = PermissionUtils::getMapAction(null);
                $this->cache[$id][$config->getType()][$field][$operation] = true;
            }

            foreach ($config->getFields() as $fieldConfig) {
                foreach ($fieldConfig->getOperations() as $operation) {
                    $this->cache[$id][$config->getType()][$fieldConfig->getField()][$operation] = true;
                }
            }
        }
    }

    /**
     * Check if the access is granted by a sharing entry.
     *
     * @param string                        $operation The operation
     * @param SubjectIdentityInterface|null $subject   The subject
     * @param string|null                   $field     The field of subject
     *
     * @return bool
     */
    private function isSharingGranted($operation, $subject = null, $field = null)
    {
        return null !== $this->sharingManager
            ? $this->sharingManager->isGranted($operation, $subject, $field)
            : false;
    }
}
