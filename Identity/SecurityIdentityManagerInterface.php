<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Identity;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Interface to retrieving security identities from tokens.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface SecurityIdentityManagerInterface
{
    /**
     * Add the special role.
     *
     * @param Role $role The special role
     *
     * @return self
     */
    public function addSpecialRole(Role $role);

    /**
     * Retrieves the available security identities for the given token.
     *
     * @param TokenInterface|null $token The token
     *
     * @return SecurityIdentityInterface[] The security identities
     */
    public function getSecurityIdentities(TokenInterface $token = null);
}
