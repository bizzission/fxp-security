<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Sharing;

/**
 * Sharing factory interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface SharingFactoryInterface
{
    /**
     * Create the sharing subject configurations.
     *
     * @throws
     *
     * @return SharingSubjectConfigCollection
     */
    public function createSubjectConfigurations(): SharingSubjectConfigCollection;

    /**
     * Create the sharing identity configurations.
     *
     * @throws
     *
     * @return SharingIdentityConfigCollection
     */
    public function createIdentityConfigurations(): SharingIdentityConfigCollection;
}
