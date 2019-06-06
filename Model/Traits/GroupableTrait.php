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
use Fxp\Component\Security\Model\GroupInterface;

/**
 * Trait for groupable model.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
trait GroupableTrait
{
    /**
     * @var Collection|GroupInterface[]
     *
     * @ORM\ManyToMany(
     *     targetEntity="Fxp\Component\Security\Model\GroupInterface",
     *     cascade={"persist"}
     * )
     * @ORM\JoinTable(
     *     joinColumns={
     *         @ORM\JoinColumn(onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(onDelete="CASCADE", name="group_id")
     *     }
     * )
     */
    protected $groups;

    /**
     * {@inheritdoc}
     */
    public function getGroups(): Collection
    {
        return $this->groups ?: $this->groups = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupNames(): array
    {
        $names = [];

        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasGroup(string $name): bool
    {
        return \in_array($name, $this->getGroupNames(), true);
    }
}
