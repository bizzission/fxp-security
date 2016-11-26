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

/**
 * Sharing manager Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface SharingManagerInterface
{
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
}
