<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Model\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fxp\Component\Security\Model\RoleHierarchicalInterface;
use Fxp\Component\Security\Model\RoleInterface;

/**
 * Trait of hierarchical for role model.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
trait RoleHierarchicalTrait
{
    /**
     * @var Collection|null
     *
     * @ORM\ManyToMany(
     *     targetEntity="Fxp\Component\Security\Model\RoleInterface",
     *     mappedBy="children"
     * )
     */
    protected $parents;

    /**
     * @var Collection|null
     *
     * @ORM\ManyToMany(
     *     targetEntity="Fxp\Component\Security\Model\RoleInterface",
     *     inversedBy="parents"
     * )
     * @ORM\JoinTable(
     *     name="role_children",
     *     joinColumns={
     *         @ORM\JoinColumn(onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(onDelete="CASCADE", name="children_role_id")
     *     }
     * )
     */
    protected $children;

    /**
     * {@inheritdoc}
     */
    public function addParent(RoleHierarchicalInterface $role)
    {
        /* @var RoleHierarchicalInterface $self */
        $self = $this;
        $role->addChild($self);
        $this->getParents()->add($role);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeParent(RoleHierarchicalInterface $parent)
    {
        if ($this->getParents()->contains($parent)) {
            $this->getParents()->removeElement($parent);
            $parent->getChildren()->removeElement($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParents()
    {
        return $this->parents ?: $this->parents = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getParentNames()
    {
        $names = [];

        /* @var RoleInterface $parent */
        foreach ($this->getParents() as $parent) {
            $names[] = $parent->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParent($name)
    {
        return \in_array($name, $this->getParentNames());
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(RoleHierarchicalInterface $role)
    {
        $this->getChildren()->add($role);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild(RoleHierarchicalInterface $child)
    {
        if ($this->getChildren()->contains($child)) {
            $this->getChildren()->removeElement($child);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->children ?: $this->children = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenNames()
    {
        $names = [];

        /* @var RoleInterface $child */
        foreach ($this->getChildren() as $child) {
            $names[] = $child->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChild($name)
    {
        return \in_array($name, $this->getChildrenNames());
    }
}
