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
use Fxp\Component\Security\Model\RoleInterface;
use Fxp\Component\Security\Model\Traits\RoleableInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
final class RoleSecurityIdentity extends AbstractSecurityIdentity
{
    /**
     * Creates a role security identity from a RoleInterface.
     *
     * @param RoleInterface|string $role The role
     *
     * @return self
     */
    public static function fromAccount($role)
    {
        return $role instanceof RoleInterface
            ? new self(ClassUtils::getClass($role), $role->getName())
            : new self('role', $role);
    }

    /**
     * Creates a role security identity from a TokenInterface.
     *
     * @param TokenInterface $token The token
     *
     * @throws InvalidArgumentException When the user class not implements "Fxp\Component\Security\Model\Traits\RoleableInterface"
     *
     * @return self[]
     */
    public static function fromToken(TokenInterface $token)
    {
        $user = $token->getUser();

        if ($user instanceof RoleableInterface) {
            $sids = [];
            $roles = $user->getRoles();

            foreach ($roles as $role) {
                $sids[] = self::fromAccount($role);
            }

            return $sids;
        }

        throw new InvalidArgumentException('The user class must implement "Fxp\Component\Security\Model\Traits\RoleableInterface"');
    }
}
