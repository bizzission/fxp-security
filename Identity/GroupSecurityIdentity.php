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

use Doctrine\Common\Util\ClassUtils;
use Sonatra\Component\Security\Exception\InvalidArgumentException;
use Sonatra\Component\Security\Model\GroupInterface;
use Sonatra\Component\Security\Model\Traits\GroupableInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class GroupSecurityIdentity extends AbstractSecurityIdentity
{
    /**
     * Creates a group security identity from a GroupInterface.
     *
     * @param GroupInterface $group The group
     *
     * @return self
     */
    public static function fromAccount(GroupInterface $group)
    {
        return new self(ClassUtils::getClass($group), $group->getGroup());
    }

    /**
     * Creates a group security identity from a TokenInterface.
     *
     * @param TokenInterface $token The token
     *
     * @return self[]
     *
     * @throws InvalidArgumentException When the user class not implements "Sonatra\Component\Security\Model\Traits\GroupableInterface"
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

        throw new InvalidArgumentException('The user class must implement "Sonatra\Component\Security\Model\Traits\GroupableInterface"');
    }
}
