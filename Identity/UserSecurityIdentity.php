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

use Sonatra\Component\Security\Exception\InvalidArgumentException;
use Sonatra\Component\Security\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class UserSecurityIdentity extends AbstractSecurityIdentity
{
    const TYPE = 'user';

    /**
     * Creates a user security identity from a UserInterface.
     *
     * @param UserInterface $user The user
     *
     * @return self
     */
    public static function fromAccount(UserInterface $user)
    {
        return new self($user->getUsername());
    }

    /**
     * Creates a user security identity from a TokenInterface.
     *
     * @param TokenInterface $token The token
     *
     * @return self[]
     *
     * @throws InvalidArgumentException When the user class not implements "Sonatra\Component\Security\Model\UserInterface"
     */
    public static function fromToken(TokenInterface $token)
    {
        $user = $token->getUser();

        if ($user instanceof UserInterface) {
            return self::fromAccount($user);
        }

        throw new InvalidArgumentException('The user class must implement "Sonatra\Component\Security\Model\UserInterface"');
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
