<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Sharing;

use Sonatra\Component\Security\Identity\SubjectIdentityInterface;

/**
 * Sharing manager Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface SharingManagerInterface
{
    /**
     * Add the sharing subject config.
     *
     * @param SharingSubjectConfigInterface $config The sharing subject config
     *
     * @return self
     */
    public function addSubjectConfig(SharingSubjectConfigInterface $config);

    /**
     * Check if the sharing subject config is present.
     *
     * @param string $class The class name of sharing subject
     *
     * @return bool
     */
    public function hasSubjectConfig($class);

    /**
     * Get the sharing subject config.
     *
     * @param string $class The class name of sharing subject
     *
     * @return SharingSubjectConfigInterface
     */
    public function getSubjectConfig($class);

    /**
     * Get the sharing subject configs.
     *
     * @return SharingSubjectConfigInterface[]
     */
    public function getSubjectConfigs();

    /**
     * Check if the subject has sharing visibility.
     *
     * @param SubjectIdentityInterface $subject The subject
     *
     * @return bool
     */
    public function hasSharingVisibility(SubjectIdentityInterface $subject);

    /**
     * Add the sharing identity config.
     *
     * @param SharingIdentityConfigInterface $config The sharing identity config
     *
     * @return self
     */
    public function addIdentityConfig(SharingIdentityConfigInterface $config);

    /**
     * Check if the sharing identity config is present.
     *
     * @param string $class The class name of sharing identity
     *
     * @return bool
     */
    public function hasIdentityConfig($class);

    /**
     * Get the sharing identity config.
     *
     * @param string $class The class name of sharing identity
     *
     * @return SharingIdentityConfigInterface
     */
    public function getIdentityConfig($class);

    /**
     * Get the sharing identity configs.
     *
     * @return SharingIdentityConfigInterface[]
     */
    public function getIdentityConfigs();

    /**
     * Check if there is an identity config with the roleable option.
     *
     * @return bool
     */
    public function hasIdentityRoleable();

    /**
     * Check if there is an identity config with the permissible option.
     *
     * @return bool
     */
    public function hasIdentityPermissible();

    /**
     * Check if the access is granted by a sharing entry.
     *
     * @param string                        $operation The operation
     * @param SubjectIdentityInterface|null $subject   The subject
     * @param string|null                   $field     The field of subject
     *
     * @return bool
     */
    public function isGranted($operation, $subject = null, $field = null);

    /**
     * Preload permissions of objects.
     *
     * @param object[] $objects The objects
     *
     * @return self
     */
    public function preloadPermissions(array $objects);

    /**
     * Preload the permissions of sharing roles.
     *
     * @param SubjectIdentityInterface[] $subjects The subjects
     */
    public function preloadRolePermissions(array $subjects);

    /**
     * Reset the preload permissions of objects.
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
