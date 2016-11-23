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
use Sonatra\Component\Security\Identity\SecurityIdentityRetrievalStrategyInterface;
use Sonatra\Component\Security\Identity\SubjectIdentityInterface;
use Sonatra\Component\Security\Identity\SubjectUtils;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Permission manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionManager implements PermissionManagerInterface
{
    /**
     * @var SecurityIdentityRetrievalStrategyInterface
     */
    protected $sidRetrievalStrategy;

    /**
     * @var array
     */
    protected $configs;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * Constructor.
     *
     * @param SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy The security identity retrieval strategy
     * @param PermissionConfigInterface[]                $configs              The permission configs
     */
    public function __construct(SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy,
                                array $configs = array())
    {
        $this->sidRetrievalStrategy = $sidRetrievalStrategy;
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
    public function getSecurityIdentities(TokenInterface $token = null)
    {
        if (null === $token) {
            return array();
        }

        return $this->sidRetrievalStrategy->getSecurityIdentities($token);
    }

    /**
     * {@inheritdoc}
     */
    public function isManaged($subject)
    {
        try {
            /* @var SubjectIdentityInterface $subject */
            list($subject, $field) = $this->getSubjectAndField($subject);

            if ($this->hasConfig($subject->getType())) {
                if (null === $field) {
                    return true;
                } else {
                    $config = $this->getConfig($subject->getType());

                    return in_array($field, $config->getFields());
                }
            }
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
    public function isGranted(array $sids, $subject, $permissions)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isFieldGranted(array $sids, $subject, $field, $permissions)
    {
        return $this->isGranted($sids, new FieldVote($subject, $field), $permissions);
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
     * Get the subject identity and field.
     *
     * @param FieldVote|SubjectIdentityInterface|object|string $subject The subject instance or classname
     *
     * @return array
     */
    private function getSubjectAndField($subject)
    {
        if ($subject instanceof FieldVote) {
            $field = $subject->getField();
            $subject = $subject->getSubject();
        } else {
            $field = null;
            $subject = SubjectUtils::getSubjectIdentity($subject);
        }

        return array($subject, $field);
    }
}
