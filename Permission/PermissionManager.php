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

use Doctrine\Common\Util\ClassUtils;
use Sonatra\Component\Security\Event\CheckPermissionEvent;
use Sonatra\Component\Security\Event\PostLoadPermissionsEvent;
use Sonatra\Component\Security\Event\PreLoadPermissionsEvent;
use Sonatra\Component\Security\Exception\PermissionConfigNotFoundException;
use Sonatra\Component\Security\Exception\InvalidSubjectIdentityException;
use Sonatra\Component\Security\Exception\UnexpectedTypeException;
use Sonatra\Component\Security\Identity\IdentityUtils;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\SubjectIdentity;
use Sonatra\Component\Security\Identity\SubjectIdentityInterface;
use Sonatra\Component\Security\Identity\SubjectUtils;
use Sonatra\Component\Security\PermissionEvents;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Sonatra\Component\Security\Sharing\SharingUtils;
use Sonatra\Component\Security\SharingTypes;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Permission manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionManager implements PermissionManagerInterface
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
    protected $configs;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var array
     */
    protected $cache = array();

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
     * Constructor.
     *
     * @param EventDispatcherInterface     $dispatcher     The event dispatcher
     * @param PermissionProviderInterface  $provider       The permission provider
     * @param SharingManagerInterface|null $sharingManager The sharing manager
     * @param PermissionConfigInterface[]  $configs        The permission configs
     */
    public function __construct(EventDispatcherInterface $dispatcher,
                                PermissionProviderInterface $provider,
                                SharingManagerInterface $sharingManager = null,
                                array $configs = array())
    {
        $this->dispatcher = $dispatcher;
        $this->provider = $provider;
        $this->sharingManager = $sharingManager;
        $this->configs = array();

        foreach ($configs as $config) {
            $this->addConfig($config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function enable()
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function disable()
    {
        $this->enabled = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addConfig(PermissionConfigInterface $config)
    {
        $this->configs[$config->getType()] = $config;
        unset($this->cacheSubjectType[$config->getType()]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasConfig($class)
    {
        return isset($this->configs[ClassUtils::getRealClass($class)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($class)
    {
        $class = ClassUtils::getRealClass($class);

        if (!$this->hasConfig($class)) {
            throw new PermissionConfigNotFoundException($class);
        }

        return $this->configs[$class];
    }

    /**
     * {@inheritdoc}
     */
    public function isManaged($subject)
    {
        try {
            /* @var SubjectIdentityInterface $subject */
            list($subject, $field) = $this->getSubjectAndField($subject);

            return $this->doIsManaged($subject, $field);
        } catch (InvalidSubjectIdentityException $e) {
            // do nothing
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isFieldManaged($subject, $field)
    {
        return $this->isManaged(new FieldVote($subject, $field));
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(array $sids, $permissions, $subject = null)
    {
        try {
            /* @var SubjectIdentityInterface|null $subject */
            list($subject, $field) = $this->getSubjectAndField($subject, true);
            $permissions = (array) $permissions;

            if (null !== $subject && !$this->doIsManaged($subject, $field)) {
                return true;
            }

            return $this->doIsGranted($sids, $permissions, $subject, $field);
        } catch (InvalidSubjectIdentityException $e) {
            // do nothing
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isFieldGranted(array $sids, $permissions, $subject, $field)
    {
        return $this->isGranted($sids, $permissions, new FieldVote($subject, $field));
    }

    /**
     * {@inheritdoc}
     */
    public function preloadPermissions(array $objects)
    {
        $subjects = array();

        foreach ($objects as $object) {
            $subject = SubjectIdentity::fromObject($object);
            $id = SubjectUtils::getCacheId($subject);

            if ($this->hasConfig($subject->getType())
                    && $this->hasSharingPermissions($subject)
                    && !array_key_exists($id, $this->cacheSubjectSharing)) {
                $subjects[$id] = $subject;
                $this->cacheSubjectSharing[$id] = false;
            }
        }

        $res = $this->provider->getSharingEntries(array_values($subjects));
        $entries = array();

        foreach ($res as $sharing) {
            $id = SubjectUtils::getSharingCacheId($sharing);
            $entries[$id] = $sharing;
        }

        foreach ($subjects as $id => $subject) {
            if (isset($entries[$id])) {
                $this->cacheSubjectSharing[$id] = array(
                    'sharing' => $entries[$id],
                    'operations' => SharingUtils::buildOperations($entries[$id]),
                );
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resetPreloadPermissions(array $objects)
    {
        foreach ($objects as $object) {
            $subject = SubjectIdentity::fromObject($object);
            $id = SubjectUtils::getCacheId($subject);
            unset($this->cacheRoleSharing[$id]);
            unset($this->cacheSubjectSharing[$id]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->cache = array();
        $this->cacheRoleSharing = array();
        $this->cacheSubjectSharing = array();

        return $this;
    }

    /**
     * Get the subject identity and field.
     *
     * @param FieldVote|SubjectIdentityInterface|object|string|null $subject  The subject instance or classname
     * @param bool                                                  $optional Check if the subject id optional
     *
     * @return array
     */
    private function getSubjectAndField($subject, $optional = false)
    {
        if ($subject instanceof FieldVote) {
            $field = $subject->getField();
            $subject = $subject->getSubject();
        } else {
            if (null === $subject && !$optional) {
                throw new UnexpectedTypeException($subject, 'FieldVote|SubjectIdentityInterface|object|string');
            }

            $field = null;
            $subject = null !== $subject
                ? SubjectUtils::getSubjectIdentity($subject)
                : null;
        }

        return array($subject, $field);
    }

    /**
     * Action to check if the subject is managed.
     *
     * @param SubjectIdentityInterface $subject The subject identity
     * @param string|null              $field   The field name
     *
     * @return bool
     */
    private function doIsManaged(SubjectIdentityInterface $subject, $field = null)
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
    private function doIsGranted(array $sids, array $permissions, $subject = null, $field = null)
    {
        if (null !== $subject) {
            $this->preloadPermissions(array($subject));
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
     * Check if the subject has permissions defined in sharing entries.
     *
     * @param SubjectIdentityInterface $subject The subject
     *
     * @return bool
     */
    private function hasSharingPermissions(SubjectIdentityInterface $subject)
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
