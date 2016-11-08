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
 * Organization user interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface OrganizationUserInterface
{
    /**
     * Set the organization.
     *
     * @param OrganizationInterface $organization The organization
     *
     * @return self
     */
    public function setOrganization($organization);

    /**
     * Get the organization.
     *
     * @return OrganizationInterface
     */
    public function getOrganization();

    /**
     * Set the user of organization.
     *
     * @param UserInterface|null $user The user of organization
     *
     * @return self
     */
    public function setUser($user);

    /**
     * Get the user of organization.
     *
     * @return UserInterface|null
     */
    public function getUser();

    /**
     * @return string
     */
    public function __toString();
}
