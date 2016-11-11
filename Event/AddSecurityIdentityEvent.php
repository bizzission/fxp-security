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

/**
 * The add security identity retrieval strategy event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AddSecurityIdentityEvent extends Event
{
    use SecurityIdentityEventTrait;

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
