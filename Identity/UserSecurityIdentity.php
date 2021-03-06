<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Identity;

use Fxp\Component\DoctrineExtra\Util\ClassUtils;
use Fxp\Component\Security\Exception\InvalidArgumentException;
use Fxp\Component\Security\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
final class UserSecurityIdentity extends AbstractSecurityIdentity
{
    /**
     * Creates a user security identity from a UserInterface.
     *
     * @param UserInterface $user The user
     *
     * @return UserSecurityIdentity
     */
    public static function fromAccount(UserInterface $user): UserSecurityIdentity
    {
        return new self(ClassUtils::getClass($user), $user->getUsername());
    }

    /**
     * Creates a user security identity from a TokenInterface.
     *
     * @param TokenInterface $token The token
     *
     * @throws InvalidArgumentException When the user class not implements "Fxp\Component\Security\Model\UserInterface"
     *
     * @return UserSecurityIdentity
     */
    public static function fromToken(TokenInterface $token): UserSecurityIdentity
    {
        $user = $token->getUser();

        if ($user instanceof UserInterface) {
            return self::fromAccount($user);
        }

        throw new InvalidArgumentException('The user class must implement "Fxp\Component\Security\Model\UserInterface"');
    }
}
