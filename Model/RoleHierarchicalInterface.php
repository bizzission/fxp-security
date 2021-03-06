<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Model;

use Doctrine\Common\Collections\Collection;

/**
 * Interface for role hierarchical.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface RoleHierarchicalInterface extends RoleInterface
{
    /**
     * Add a parent on the current role.
     *
     * @param RoleHierarchicalInterface $role
     *
     * @return static
     */
    public function addParent(RoleHierarchicalInterface $role);

    /**
     * Remove a parent on the current role.
     *
     * @param RoleHierarchicalInterface $parent
     *
     * @return static
     */
    public function removeParent(RoleHierarchicalInterface $parent);

    /**
     * Gets all parent.
     *
     * @return Collection|RoleHierarchicalInterface[]
     */
    public function getParents();

    /**
     * Gets all parent names.
     *
     * @return array
     */
    public function getParentNames(): array;

    /**
     * Check if role has parent.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasParent(string $name): bool;

    /**
     * Add a child on the current role.
     *
     * @param RoleHierarchicalInterface $role
     *
     * @return static
     */
    public function addChild(RoleHierarchicalInterface $role);

    /**
     * Remove a child on the current role.
     *
     * @param RoleHierarchicalInterface $child
     *
     * @return static
     */
    public function removeChild(RoleHierarchicalInterface $child);

    /**
     * Gets all children.
     *
     * @return Collection|RoleHierarchicalInterface[]
     */
    public function getChildren();

    /**
     * Gets all children names.
     *
     * @return string[]
     */
    public function getChildrenNames(): array;

    /**
     * Check if role has child.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasChild(string $name): bool;
}
