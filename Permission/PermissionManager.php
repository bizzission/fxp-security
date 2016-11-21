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

use Sonatra\Component\Security\Authorization\Voter\FieldVote;
use Sonatra\Component\Security\Identity\SecurityIdentityRetrievalStrategyInterface;
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
     * @var bool
     */
    protected $enabled = true;

    /**
     * Constructor.
     *
     * @param SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy The security identity retrieval strategy
     */
    public function __construct(SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy)
    {
        $this->sidRetrievalStrategy = $sidRetrievalStrategy;
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
    public function isManaged($domainObject)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isFieldManaged($domainObject, $field)
    {
        return $this->isManaged(new FieldVote($domainObject, $field));
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(array $sids, $domainObject, $permissions)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isFieldGranted(array $sids, $domainObject, $field, $permissions)
    {
        return $this->isGranted($sids, new FieldVote($domainObject, $field), $permissions);
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
}
