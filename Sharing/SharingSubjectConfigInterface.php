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
 * Sharing subject config Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface SharingSubjectConfigInterface
{
    /**
     * Get the type. Typically, this is the PHP class name.
     *
     * @return string
     */
    public function getType();

    /**
     * Get the sharing visibility.
     *
     * @return string
     */
    public function getVisibility();
}
