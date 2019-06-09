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
     * @return SharingSubjectConfigInterface[]
     */
    public function createSubjectConfigurations(): array;

    /**
     * Create the sharing identity configurations.
     *
     * @return SharingIdentityConfigInterface[]
     */
    public function createIdentityConfigurations(): array;
}
