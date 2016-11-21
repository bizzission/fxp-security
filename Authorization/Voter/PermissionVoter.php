<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Authorization\Voter;

use Sonatra\Component\Security\Identity\SecurityIdentityRetrievalStrategyInterface;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Permission voter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionVoter extends Voter
{
    /**
     * @var PermissionManagerInterface
     */
    private $permissionManager;

    /**
     * @var SecurityIdentityRetrievalStrategyInterface
     */
    private $sidStrategy;

    /**
     * Constructor.
     *
     * @param PermissionManagerInterface                 $permissionManager The permission manager
     * @param SecurityIdentityRetrievalStrategyInterface $sidStrategy       The security identity retrieval strategy
     */
    public function __construct(PermissionManagerInterface $permissionManager,
                                SecurityIdentityRetrievalStrategyInterface $sidStrategy)
    {
        $this->permissionManager = $permissionManager;
        $this->sidStrategy = $sidStrategy;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!$this->isAttributeSupported($attribute) || !$this->isSubjectSupported($subject)) {
            return false;
        }

        return $this->permissionManager->isManaged($subject);
    }

    /**
     * Check if the attribute is supported.
     *
     * @param string $attribute The attribute
     *
     * @return bool
     */
    protected function isAttributeSupported($attribute)
    {
        return is_string($attribute) && 0 === strpos(strtolower($attribute), 'perm_');
    }

    /**
     * Check if the subject is supported.
     *
     * @param FieldVote|mixed $subject The subject
     *
     * @return bool
     */
    protected function isSubjectSupported($subject)
    {
        return is_string($subject) || $subject instanceof FieldVote || is_object($subject);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $sids = $this->sidStrategy->getSecurityIdentities($token);
        $attribute = substr($attribute, 5);

        return $this->permissionManager->isGranted($sids, $subject, $attribute);
    }
}
