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

use Sonatra\Component\Security\Model\SharingInterface;

/**
 * Sharing utils.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class SharingUtils
{
    /**
     * Build the operations of sharing entry.
     *
     * @param SharingInterface $sharing The sharing entry
     *
     * @return string[]
     */
    public static function buildOperations(SharingInterface $sharing)
    {
        $operations = array();

        foreach ($sharing->getPermissions() as $permission) {
            $operations[] = $permission->getOperation();
        }

        return $operations;
    }
}
