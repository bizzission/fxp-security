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
use Fxp\Component\Security\Model\OrganizationUserInterface;
use Fxp\Component\Security\Model\UserInterface;

/**
 * Trait for organization model.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
trait OrganizationTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $name;

    /**
     * @var null|UserInterface
     *
     * @ORM\OneToOne(
     *     targetEntity="Fxp\Component\Security\Model\UserInterface",
     *     inversedBy="organization",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var null|Collection|OrganizationUserInterface[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Fxp\Component\Security\Model\OrganizationUserInterface",
     *     mappedBy="organization",
     *     fetch="EXTRA_LAZY",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $organizationUsers;

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function isUserOrganization()
    {
        return null !== $this->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationUsers()
    {
        return $this->organizationUsers ?: $this->organizationUsers = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationUserNames()
    {
        $names = [];
        foreach ($this->getOrganizationUsers() as $orgUser) {
            $names[] = $orgUser->getUser()->getUsername();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOrganizationUser($username)
    {
        return \in_array($username, $this->getOrganizationUserNames(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function addOrganizationUser(OrganizationUserInterface $organizationUser)
    {
        if (!$this->isUserOrganization()
            && !$this->getOrganizationUsers()->contains($organizationUser)) {
            $this->getOrganizationUsers()->add($organizationUser);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOrganizationUser(OrganizationUserInterface $organizationUser)
    {
        if ($this->getOrganizationUsers()->contains($organizationUser)) {
            $this->getOrganizationUsers()->removeElement($organizationUser);
        }

        return $this;
    }
}
