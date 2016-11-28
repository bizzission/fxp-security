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

use Sonatra\Component\Security\Exception\PermissionConfigNotFoundException;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\SubjectIdentityInterface;

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
     * Add the permission config.
     *
     * @param PermissionConfigInterface $config The permission config
     */
    public function addConfig(PermissionConfigInterface $config);

    /**
     * Check if the configuration of permission is present.
     *
     * @param string $class The class name
     *
     * @return bool
     */
    public function hasConfig($class);

    /**
     * Get the configuration of permission.
     *
     * @param string $class The class name
     *
     * @return PermissionConfigInterface
     *
     * @throws PermissionConfigNotFoundException When the configuration of permission is not found
     */
    public function getConfig($class);

    /**
     * Check if the subject is managed.
     *
     * @param SubjectIdentityInterface|FieldVote|object|string $subject The object or class name
     *
     * @return bool
     */
    public function isManaged($subject);

    /**
     * Check if the field of subject is managed.
     *
     * @param SubjectIdentityInterface|object|string $subject The object or class name
     * @param string                                 $field   The field
     *
     * @return bool
     */
    public function isFieldManaged($subject, $field);

    /**
     * Determines whether access is granted.
     *
     * @param SecurityIdentityInterface[]                           $sids        The security identities
     * @param string|string[]                                       $permissions The permissions
     * @param SubjectIdentityInterface|FieldVote|object|string|null $subject     The object or class name or field vote
     *
     * @return bool
     */
    public function isGranted(array $sids, $permissions, $subject = null);

    /**
     * Determines whether access is granted.
     *
     * @param SecurityIdentityInterface[]            $sids        The security identities
     * @param string|string[]                        $permissions The permissions
     * @param SubjectIdentityInterface|object|string $subject     The object or class name
     * @param string                                 $field       The field
     *
     * @return bool
     */
    public function isFieldGranted(array $sids, $permissions, $subject, $field);

    /**
     * Preload permissions of objects.
     *
     * @param object[] $objects The objects
     *
     * @return self
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

    /**
     * Clear all permission caches.
     *
     * @return self
     */
    public function clear();
}
