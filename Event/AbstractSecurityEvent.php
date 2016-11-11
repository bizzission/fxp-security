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

use Symfony\Component\EventDispatcher\Event;

/**
 * The abstract commit object filter event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractSecurityEvent extends Event
{
    /**
     * @var bool
     */
    protected $aclEnabled = true;

    /**
     * Defined if the acl must be enable or not.
     *
     * @param bool $enabled The value
     */
    public function setAclEnabled($enabled)
    {
        $this->aclEnabled = (bool) $enabled;
    }

    /**
     * Check if the acl is enabled.
     *
     * @return bool
     */
    public function isAclEnabled()
    {
        return $this->aclEnabled;
    }
}
