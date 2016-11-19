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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sonatra\Component\Security\Model\GroupInterface;

/**
 * Trait of groups in organization model.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
trait OrganizationGroupsTrait
{
    /**
     * @var Collection|null
     */
    protected $organizationGroups;

    /**
     * {@inheritdoc}
     */
    public function getOrganizationGroups()
    {
        return $this->organizationGroups ?: $this->organizationGroups = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationGroupNames()
    {
        $names = array();
        foreach ($this->getOrganizationGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOrganizationGroup($group)
    {
        return in_array($group, $this->getOrganizationGroupNames());
    }

    /**
     * {@inheritdoc}
     */
    public function addOrganizationGroup(GroupInterface $group)
    {
        if (!$this->isUserOrganization()
            && !$this->getOrganizationGroups()->contains($group)) {
            $this->getOrganizationGroups()->add($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOrganizationGroup(GroupInterface $group)
    {
        if ($this->getOrganizationGroups()->contains($group)) {
            $this->getOrganizationGroups()->removeElement($group);
        }

        return $this;
    }
}
