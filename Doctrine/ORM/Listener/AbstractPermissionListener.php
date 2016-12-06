<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Doctrine\ORM\Listener;

/**
 * Abstract class for permission listeners.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractPermissionListener extends AbstractListener
{
    /**
     * @var array
     */
    protected $postResetPermissions = array();

    /**
     * Reset the preloaded permissions used for the insertions.
     */
    public function postFlush()
    {
        $this->getPermissionManager()->resetPreloadPermissions($this->postResetPermissions);
        $this->postResetPermissions = array();
    }
}
