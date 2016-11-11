<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Event;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * The security identity retrieval strategy event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SecurityIdentityEvent extends AbstractSecurityEvent
{
    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var \Symfony\Component\Security\Acl\Model\SecurityIdentityInterface[]
     */
    protected $securityIdentities;

    /**
     * Constructor.
     *
     * @param TokenInterface                                                    $token              The token
     * @param \Symfony\Component\Security\Acl\Model\SecurityIdentityInterface[] $securityIdentities The security identities
     */
    public function __construct(TokenInterface $token, array $securityIdentities = array())
    {
        $this->token = $token;
        $this->securityIdentities = $securityIdentities;
    }

    /**
     * Get the token.
     *
     * @return TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set security identities.
     *
     * @param \Symfony\Component\Security\Acl\Model\SecurityIdentityInterface[] $securityIdentities The security identities
     */
    public function setSecurityIdentities(array $securityIdentities)
    {
        $this->securityIdentities = $securityIdentities;
    }

    /**
     * Get security identities.
     *
     * @return \Symfony\Component\Security\Acl\Model\SecurityIdentityInterface[]
     */
    public function getSecurityIdentities()
    {
        return $this->securityIdentities;
    }
}
