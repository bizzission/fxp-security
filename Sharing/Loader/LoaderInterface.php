<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Sharing\Loader;

use Fxp\Component\Security\Sharing\SharingIdentityConfigInterface;
use Fxp\Component\Security\Sharing\SharingSubjectConfigInterface;

/**
 * Sharing loader interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Load the sharing subject configurations.
     *
     * @return SharingSubjectConfigInterface[]
     */
    public function loadSubjectConfigurations(): array;

    /**
     * Load the sharing identity configurations.
     *
     * @return SharingIdentityConfigInterface[]
     */
    public function loadIdentityConfigurations(): array;
}
