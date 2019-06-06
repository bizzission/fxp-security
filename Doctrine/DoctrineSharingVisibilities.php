<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Doctrine;

use Fxp\Component\Security\Doctrine\ORM\Event\GetNoneFilterEvent;
use Fxp\Component\Security\Doctrine\ORM\Event\GetPrivateFilterEvent;
use Fxp\Component\Security\Doctrine\ORM\Event\GetPublicFilterEvent;
use Fxp\Component\Security\SharingVisibilities;

/**
 * The doctrine sharing visibilities.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class DoctrineSharingVisibilities
{
    public static $classMap = [
        SharingVisibilities::TYPE_NONE => GetNoneFilterEvent::class,
        SharingVisibilities::TYPE_PUBLIC => GetPublicFilterEvent::class,
        SharingVisibilities::TYPE_PRIVATE => GetPrivateFilterEvent::class,
    ];
}
