<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Core\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Acl\Model\DomainObjectInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Sonatra\Component\Security\Acl\Model\AclManagerInterface;

/**
 * AclVoter to determine the roles granted on object, object field, class, or
 * class field to the token before voting.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclVoter extends Voter
{
    /**
     * @var AclManagerInterface
     */
    private $aclManager;

    /**
     * @var SecurityIdentityRetrievalStrategyInterface
     */
    private $sidRetrievalStrategy;

    /**
     * Constructor.
     *
     * @param AclManagerInterface                        $aclManager           The acl manager
     * @param SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy The security identity retrieval strategy
     */
    public function __construct(AclManagerInterface $aclManager,
                                SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy)
    {
        $this->aclManager = $aclManager;
        $this->sidRetrievalStrategy = $sidRetrievalStrategy;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!is_string($attribute) && !is_int($attribute)) {
            return false;
        }

        if ($subject instanceof FieldVote) {
            $subject = $subject->getDomainObject();
        }

        if (is_string($subject)
                || $subject instanceof DomainObjectInterface
                || $subject instanceof ObjectIdentityInterface) {
            return true;
        }

        return is_object($subject) && method_exists($subject, 'getId');
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $sids = $this->sidRetrievalStrategy->getSecurityIdentities($token);

        return $this->aclManager->isGranted($sids, $subject, $attribute);
    }
}
