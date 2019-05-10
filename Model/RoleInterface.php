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

use Fxp\Component\Security\Model\Traits\PermissionsInterface;

/**
 * Interface for role.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface RoleInterface extends PermissionsInterface
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
     * Sets the role name.
     *
     * @param string $name The role name
     *
     * @return static
     */
    public function setName(?string $name);

    /**
     * Gets the role name.
     *
     * @return string the role name
     */
    public function getName(): ?string;
}
