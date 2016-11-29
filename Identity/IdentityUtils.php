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

/**
 * Identity utils.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class IdentityUtils
{
    /**
     * Filter the role identities and convert to role instances.
     *
     * @param SecurityIdentityInterface[] $sids The security identities
     *
     * @return string[]
     */
    public static function filterRolesIdentities(array $sids)
    {
        $roles = array();

        foreach ($sids as $sid) {
            if ($sid instanceof RoleSecurityIdentity && false === strpos($sid->getIdentifier(), 'IS_')) {
                $roles[] = $sid->getIdentifier();
            }
        }

        return $roles;
    }

    /**
     * Check if the security identity is valid.
     *
     * @param SecurityIdentityInterface $sid The security identity
     *
     * @return bool
     */
    public static function isValid(SecurityIdentityInterface $sid)
    {
        return !$sid instanceof RoleSecurityIdentity
            || ($sid instanceof RoleSecurityIdentity && false === strpos($sid->getIdentifier(), 'IS_'));
    }
}
