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
use Sonatra\Component\Security\Exception\PermissionConfigNotFoundException;
use Sonatra\Component\Security\Exception\InvalidSubjectIdentityException;
use Sonatra\Component\Security\Exception\UnexpectedTypeException;
use Sonatra\Component\Security\Identity\IdentityUtils;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\SubjectIdentityInterface;
use Sonatra\Component\Security\Identity\SubjectUtils;

/**
 * Permission manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionManager implements PermissionManagerInterface
{
    /**
     * @var PermissionProviderInterface
     */
    protected $provider;

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
     * Constructor.
     *
     * @param PermissionProviderInterface $provider The permission provider
     * @param PermissionConfigInterface[] $configs  The permission configs
     */
    public function __construct(PermissionProviderInterface $provider, array $configs = array())
    {
        $this->provider = $provider;
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
        return new \SplObjectStorage();
    }

    /**
     * {@inheritdoc}
     */
    public function resetPreloadPermissions(array $objects)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->cache = array();

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
     * @param SecurityIdentityInterface[]   $sids        The security identities
     * @param string[]                      $permissions The required permissions
     * @param SubjectIdentityInterface|null $subject     The subject
     * @param string|null                   $field       The field of subject
     *
     * @return bool
     */
    private function doIsGranted(array $sids, array $permissions, $subject = null, $field = null)
    {
        $id = $this->loadPermissions($sids);

        foreach ($permissions as $operation) {
            $classAction = $this->getAction(null !== $subject ? $subject->getType() : null);
            $fieldAction = $this->getAction($field);

            if (!isset($this->cache[$id][$classAction][$fieldAction][$operation])) {
                return false;
            }
        }

        return true;
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

        if (!isset($this->cache[$id])) {
            $perms = $this->provider->getPermissions($roles);

            foreach ($perms as $perm) {
                $class = $this->getAction($perm->getClass());
                $field = $this->getAction($perm->getField());
                $this->cache[$id][$class][$field][$perm->getOperation()] = true;
            }
        }

        return $id;
    }

    /**
     * Get the action.
     *
     * @param string|null $action The action
     *
     * @return string
     */
    private function getAction($action = null)
    {
        return null !== $action
            ? $action
            : '_global';
    }
}
