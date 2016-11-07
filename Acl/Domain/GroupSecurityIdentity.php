<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Acl\Domain;

use Sonatra\Component\Security\Acl\Util\ClassUtils;
use Sonatra\Component\Security\Model\GroupableInterface;
use Sonatra\Component\Security\Model\GroupInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Sonatra\Component\Security\Exception\InvalidArgumentException;

/**
 * A SecurityIdentity implementation used for actual groups.
 *
 * For used the standard ACL Provider, the group security identity is a
 * UserSecurityIdentity with the group class name.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class GroupSecurityIdentity
{
    /**
     * Creates a group security identity from a GroupInterface.
     *
     * @param GroupInterface $group
     *
     * @return UserSecurityIdentity
     */
    public static function fromAccount(GroupInterface $group)
    {
        return new UserSecurityIdentity($group->getGroup(), ClassUtils::getRealClass($group));
    }

    /**
     * Creates a group security identity from a TokenInterface.
     *
     * @param TokenInterface $token
     *
     * @return UserSecurityIdentity[]
     *
     * @throws InvalidArgumentException When the user class not implements "Sonatra\Component\Security\Model\GroupableInterface"
     */
    public static function fromToken(TokenInterface $token)
    {
        $user = $token->getUser();

        if ($user instanceof GroupableInterface) {
            $sids = array();
            $groups = $user->getGroups();

            foreach ($groups as $group) {
                $sids[] = self::fromAccount($group);
            }

            return $sids;
        }

        throw new InvalidArgumentException('The user class must implement "Sonatra\Component\Security\Model\GroupableInterface"');
    }
}
