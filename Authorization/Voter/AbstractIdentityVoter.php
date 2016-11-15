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

use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\SecurityIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

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
            if ($this->isValidIdentity($attribute, $sid)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the security identity is valid for this voter.
     *
     * @param string                    $attribute The attribute
     * @param SecurityIdentityInterface $sid       The security identity
     *
     * @return bool
     */
    protected function isValidIdentity($attribute, SecurityIdentityInterface $sid)
    {
        return $this->getValidType() === $sid->getType()
                && substr($attribute, strlen($this->prefix)) === $sid->getIdentifier();
    }

    /**
     * Get the valid type of identity.
     *
     * @return string
     */
    abstract protected function getValidType();

    /**
     * Get the default prefix.
     *
     * @return string
     */
    abstract protected function getDefaultPrefix();
}
