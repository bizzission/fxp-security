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

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface;

/**
 * AbstractIdentityVoter to determine the identities granted on current user defined in token.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractIdentityVoter extends Voter
{
    /**
     * @var SecurityIdentityRetrievalStrategyInterface
     */
    protected $sidRetrievalStrategy;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * Constructor.
     *
     * @param SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy The security identity retrieval strategy
     * @param string|null                                $prefix               The attribute prefix
     */
    public function __construct(SecurityIdentityRetrievalStrategyInterface $sidRetrievalStrategy, $prefix = null)
    {
        $this->sidRetrievalStrategy = $sidRetrievalStrategy;
        $this->prefix = null === $prefix ? $this->getDefaultPrefix() : $prefix;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return is_string($attribute) && 0 === strpos($attribute, $this->prefix);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $sids = $this->sidRetrievalStrategy->getSecurityIdentities($token);

        foreach ($sids as $sid) {
            if ($sid instanceof UserSecurityIdentity && $this->isValidIdentity($attribute, $sid)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the security identity is valid for this voter.
     *
     * @param string               $attribute The attribute
     * @param UserSecurityIdentity $sid       The security identity
     *
     * @return bool
     */
    protected function isValidIdentity($attribute, UserSecurityIdentity $sid)
    {
        return in_array($this->getValidClass(), class_implements($sid->getClass()))
                && substr($attribute, strlen($this->prefix)) === $sid->getUsername();
    }

    /**
     * Get the valid class of identity.
     *
     * @return string
     */
    abstract protected function getValidClass();

    /**
     * Get the default prefix.
     *
     * @return string
     */
    abstract protected function getDefaultPrefix();
}
