<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Permission;

use Sonatra\Component\Security\Authorization\Voter\FieldVote;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Permission manager Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface PermissionManagerInterface
{
    /**
     * Check if permission manager is disabled.
     *
     * If the permission manager is disabled, all asked authorizations will be
     * always accepted.
     *
     * If the permission manager is enabled, all asked authorizations will be accepted
     * depending on the permissions.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Enables the permission manager (the asked authorizations will be accepted
     * depending on the permissions).
     *
     * @return self
     */
    public function enable();

    /**
     * Disables the permission manager (the asked authorizations will be always
     * accepted).
     *
     * @return self
     */
    public function disable();

    /**
     * Get the security identities of token.
     *
     * @param TokenInterface $token The token
     *
     * @return SecurityIdentityInterface[]
     */
    public function getSecurityIdentities(TokenInterface $token = null);

    /**
     * Check if the domain object is managed.
     *
     * @param FieldVote|object|string $domainObject The object or class name
     *
     * @return bool
     */
    public function isManaged($domainObject);

    /**
     * Check if the domain object is managed.
     *
     * @param object|string $domainObject The object or class name
     * @param string        $field        The field
     *
     * @return bool
     */
    public function isFieldManaged($domainObject, $field);

     * Preload permissions of objects.
     *
     * @param object[] $objects The objects
     *
     * @return \SplObjectStorage
     */
    public function preloadPermissions(array $objects);

    /**
     * Reset the preload permissions for specific objects.
     *
     * @param object[] $objects The objects
     *
     * @return self
     */
    public function resetPreloadPermissions(array $objects);
}
