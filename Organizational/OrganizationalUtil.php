<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Organizational;

use Sonatra\Component\Security\Model\Traits\OrganizationalInterface;

/**
 * Organizational Utils.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class OrganizationalUtil
{
    /**
     * Format the name with the organization name in suffix.
     *
     * @param OrganizationalInterface|object $object The organizational object
     * @param string                         $name   The name
     *
     * @return string
     */
    public static function formatName($object, $name)
    {
        return $object instanceof OrganizationalInterface && null !== $object->getOrganization()
            ? $name.'__'.$object->getOrganization()->getName()
            : $name;
    }

    /**
     * Format the organizational name with generic suffix.
     *
     * @param string $name The name
     *
     * @return string
     */
    public static function formatGeneric($name)
    {
        if (false !== ($pos = strpos($name, '__'))) {
            $name = substr($name, 0, $pos).'__org';
        }

        return $name;
    }

    /**
     * Format the organizational name without suffix.
     *
     * @param string $name The name
     *
     * @return string
     */
    public static function format($name)
    {
        if (false !== ($pos = strrpos($name, '__'))) {
            $name = substr($name, 0, $pos);
        }

        return $name;
    }
}
