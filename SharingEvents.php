<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class SharingEvents
{
    /**
     * The SharingEvents::ENABLED event occurs when the sharing manager is enabled.
     *
     * @var string
     */
    const ENABLED = 'sonatra_security.sharing.enabled';

    /**
     * The SharingEvents::ENABLED event occurs when the sharing manager is disabled.
     *
     * @var string
     */
    const DISABLED = 'sonatra_security.sharing.disabled';
}
