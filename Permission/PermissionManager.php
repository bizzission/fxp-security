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
use Sonatra\Component\Security\Identity\SubjectIdentity;
use Sonatra\Component\Security\Identity\SubjectIdentityInterface;
use Sonatra\Component\Security\Identity\SubjectUtils;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Sonatra\Component\Security\Sharing\SharingUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Permission manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionManager extends AbstractPermissionManager
{
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
    protected $cacheRoleSharing = array();

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
            list($subject, $field) = PermissionUtils::getSubjectAndField($subject);

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
            list($subject, $field) = PermissionUtils::getSubjectAndField($subject, true);
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
        $subjects = $this->buildSubjects($objects);
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
     * Convert the objects into subject identities.
     *
     * @param object[] $objects The objects
     *
     * @return SubjectIdentityInterface[]
     */
    private function buildSubjects(array $objects)
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

        return $subjects;
    }
}
