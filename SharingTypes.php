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
final class SharingTypes
{
    /**
     * The SharingTypes::TYPE_NONE type defines that no record is filtered and configured.
     *
     * @var string
     */
    const TYPE_NONE = 'none';

    /**
     * The SharingTypes::TYPE_PUBLIC type defines that no record is filtered, but records
     * can be configured.
     *
     * @var string
     */
    const TYPE_PUBLIC = 'public';

    /**
     * The SharingTypes::TYPE_PRIVATE type defines that records are filtered,
     * and only records with sharing entries are listed with their configurations.
     *
     * @var string
     */
    const TYPE_PRIVATE = 'private';
}
