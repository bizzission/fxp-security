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

use Sonatra\Component\Security\Event\Traits\SecurityIdentityEventTrait;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * The add security identity retrieval strategy event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AddSecurityIdentityEvent extends Event
{
    use SecurityIdentityEventTrait;

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
     * Set security identities.
     *
     * @param \Symfony\Component\Security\Acl\Model\SecurityIdentityInterface[] $securityIdentities The security identities
     */
    public function setSecurityIdentities(array $securityIdentities)
    {
        $this->securityIdentities = $securityIdentities;
    }
}
