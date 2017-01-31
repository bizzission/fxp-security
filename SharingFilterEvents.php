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
final class SharingFilterEvents
{
    /**
     * The SharingFilterEvents::FILTER event occurs when the sharing filter listener is triggered.
     *
     * @Event("Sonatra\Component\Security\Doctrine\ORM\Event\GetFilterEvent")
     *
     * @var string
     */
    const DOCTRINE_ORM_FILTER = 'sonatra_security.sharing.doctrine_orm.filter';

    /**
     * Build the event of sharing filter with visibility.
     *
     * @param string $eventName  The sharing filter event name
     * @param string $visibility The sharing visibility
     *
     * @return string
     */
    public static function getName($eventName, $visibility)
    {
        return $eventName.'.'.$visibility;
    }
}
