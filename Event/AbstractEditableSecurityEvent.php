<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Event;

/**
 * The abstract editable security event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractEditableSecurityEvent extends AbstractSecurityEvent
{
    /**
     * Defined if the permission manager must be enable or not.
     *
     * @param bool $enabled The value
     */
    public function setPermissionEnabled($enabled)
    {
        $this->permissionEnabled = (bool) $enabled;
    }
}
