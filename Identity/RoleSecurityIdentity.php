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
use Sonatra\Component\Security\Model\Traits\RoleableInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class RoleSecurityIdentity extends AbstractSecurityIdentity
{
    const TYPE = 'role';

    /**
     * Creates a role security identity from a RoleInterface.
     *
     * @param RoleInterface $role The role
     *
     * @return self
     */
    public static function fromAccount(RoleInterface $role)
    {
        return new self($role->getRole());
    }

    /**
     * Creates a role security identity from a TokenInterface.
     *
     * @param TokenInterface $token The token
     *
     * @return self[]
     *
     * @throws InvalidArgumentException When the user class not implements "Sonatra\Component\Security\Model\Traits\RoleableInterface"
     */
    public static function fromToken(TokenInterface $token)
    {
        $user = $token->getUser();

        if ($user instanceof RoleableInterface) {
            $sids = array();
            $roles = $user->getRoles();

            foreach ($roles as $role) {
                $sids[] = self::fromAccount($role);
            }

            return $sids;
        }

        throw new InvalidArgumentException('The user class must implement "Sonatra\Component\Security\Model\Traits\GroupableInterface"');
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
