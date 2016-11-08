<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Model\Traits;

/**
 * Groupable interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface GroupableInterface
{
    /**
     * Indicates whether the model belongs to the specified group or not.
     *
     * @param string $name The name of the group
     *
     * @return bool
     */
    public function hasGroup($name);

    /**
     * Gets the groups granted to the user.
     *
     * @return \Traversable
     */
    public function getGroups();
}
