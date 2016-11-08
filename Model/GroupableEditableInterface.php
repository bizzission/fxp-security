<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Model;

/**
 * Groupable editable interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface GroupableEditableInterface extends GroupableInterface
{
    /**
     * Add a group to the model groups.
     *
     * @param GroupInterface $group
     *
     * @return self
     */
    public function addSecurityGroup(GroupInterface $group);

    /**
     * Remove a group from the model groups.
     *
     * @param GroupInterface $group
     *
     * @return self
     */
    public function removeSecurityGroup(GroupInterface $group);
}
