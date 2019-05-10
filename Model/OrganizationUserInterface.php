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

/**
 * Organization user interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface OrganizationUserInterface
{
    /**
     * @return string
     */
    public function __toString(): string;

    /**
     * Get id.
     *
     * @return null|int|string
     */
    public function getId();

    /**
     * Set the organization.
     *
     * @param OrganizationInterface $organization The organization
     *
     * @return static
     */
    public function setOrganization(OrganizationInterface $organization);

    /**
     * Get the organization.
     *
     * @return OrganizationInterface
     */
    public function getOrganization(): ?OrganizationInterface;

    /**
     * Set the user of organization.
     *
     * @param UserInterface $user The user of organization
     *
     * @return static
     */
    public function setUser(UserInterface $user);

    /**
     * Get the user of organization.
     *
     * @return UserInterface
     */
    public function getUser(): ?UserInterface;
}
