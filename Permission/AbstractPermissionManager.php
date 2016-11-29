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
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\SubjectIdentityInterface;
use Sonatra\Component\Security\Identity\SubjectUtils;
use Sonatra\Component\Security\Model\RoleInterface;
use Sonatra\Component\Security\PermissionEvents;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Sonatra\Component\Security\SharingTypes;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Abstract permission manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractPermissionManager implements PermissionManagerInterface
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
     * @var SharingManagerInterface|null
     */
    protected $sharingManager;

    /**
     * @var array
     */
    protected $cache = array();

    /**
     * @var array
     */
    protected $cacheSharing = array();

    /**
     * @var array
     */
    protected $cacheRoleSharing = array();

    /**
     * @var array
     */
    protected $cacheSubjectType = array();

    /**
     * @var array
     */
    protected $cacheSubjectSharing = array();

    /**
     * Action to check if the subject is managed.
     *
     * @param SubjectIdentityInterface $subject The subject identity
     * @param string|null              $field   The field name
     *
     * @return bool
     */
    protected function doIsManaged(SubjectIdentityInterface $subject, $field = null)
    {
        if ($this->hasConfig($subject->getType())) {
            if (null === $field) {
                return true;
            } else {
                $config = $this->getConfig($subject->getType());

                return in_array($field, $config->getFields());
            }
        }

        return false;
    }

    /**
     * Action to determine whether access is granted.
     *
     * @param SecurityIdentityInterface[]   $sids        The security identities
     * @param string[]                      $permissions The required permissions
     * @param SubjectIdentityInterface|null $subject     The subject
     * @param string|null                   $field       The field of subject
     *
     * @return bool
     */
    protected function doIsGranted(array $sids, array $permissions, $subject = null, $field = null)
    {
        $sharingId = null;

        if (null !== $subject) {
            $this->preloadPermissions(array($subject));
            $this->loadSharingPermissions(array($subject));
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
     * Check if the subject has permissions defined in sharing entries.
     *
     * @param SubjectIdentityInterface $subject The subject
     *
     * @return bool
     */
    protected function hasSharingPermissions(SubjectIdentityInterface $subject)
    {
        if (!array_key_exists($subject->getType(), $this->cacheSubjectType)) {
            $hasSharingPermissions = false;

            if ($this->hasSharingIdentityPermissible() && $this->isManaged($subject)) {
                $config = $this->getConfig($subject->getType());
                $hasSharingPermissions = SharingTypes::TYPE_NONE !== $config->getSharingType();
            }

            $this->cacheSubjectType[$subject->getType()] = $hasSharingPermissions;
        }

        return $this->cacheSubjectType[$subject->getType()];
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

        $sharingId = null !== $subject ? SubjectUtils::getCacheId($subject) : null;
        $classAction = PermissionUtils::getMapAction(null !== $subject ? $subject->getType() : null);
        $fieldAction = PermissionUtils::getMapAction($field);

        return isset($this->cache[$id][$classAction][$fieldAction][$operation])
            || isset($this->cacheSharing[$sharingId][$classAction][$fieldAction][$operation])
            || $this->isSharingGranted($operation, $subject, $field);
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
        if (null !== $subject && null === $field) {
            $id = SubjectUtils::getCacheId($subject);

            return isset($this->cacheSubjectSharing[$id]['operations'])
            && in_array($operation, $this->cacheSubjectSharing[$id]['operations']);
        }

        return false;
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
     * Load the permissions of sharing roles.
     *
     * @param SubjectIdentityInterface[] $subjects The subjects
     */
    private function loadSharingPermissions(array $subjects)
    {
        $roles = array();
        $idSubjects = array();

        foreach ($subjects as $subject) {
            $subjectId = SubjectUtils::getCacheId($subject);
            $idSubjects[$subjectId] = $subject;

            if (!array_key_exists($subjectId, $this->cacheSharing)
                    && isset($this->cacheRoleSharing[$subjectId])) {
                $roles = array_merge($roles, $this->cacheRoleSharing[$subjectId]);
                $this->cacheSharing[$subjectId] = array();
            }
        }

        $roles = array_unique($roles);

        if (!empty($roles)) {
            $this->doLoadSharingPermissions($idSubjects, $roles);
        }
    }

    /**
     * Action to load the permissions of sharing roles.
     *
     * @param array    $idSubjects The map of subject id and subject
     * @param string[] $roles      The roles
     */
    private function doLoadSharingPermissions(array $idSubjects, array $roles)
    {
        /* @var RoleInterface[] $mapRoles */
        $mapRoles = array();
        $cRoles = $this->provider->getPermissionRoles($roles);

        foreach ($cRoles as $role) {
            $mapRoles[$role->getRole()] = $role;
        }

        /* @var SubjectIdentityInterface $subject */
        foreach ($idSubjects as $id => $subject) {
            foreach ($this->cacheRoleSharing[$id] as $role) {
                if (isset($mapRoles[$role])) {
                    $cRole = $mapRoles[$role];

                    foreach ($cRole->getPermissions() as $perm) {
                        $class = $subject->getType();
                        $field = PermissionUtils::getMapAction($perm->getField());
                        $this->cacheSharing[$id][$class][$field][$perm->getOperation()] = true;
                    }
                }
            }
        }
    }

    /**
     * Check if there is an identity config with the permissible option.
     *
     * @return bool
     */
    private function hasSharingIdentityPermissible()
    {
        return null === $this->sharingManager
            || (null !== $this->sharingManager && $this->sharingManager->hasIdentityPermissible());
    }
}
