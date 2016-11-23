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

use Symfony\Component\Security\Core\Role\RoleInterface as BaseRoleInterface;

/**
 * Interface for role.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface RoleInterface extends BaseRoleInterface
{
    /**
     * Get id.
     *
     * @return int|string|null
     */
    public function getId();

    /**
     * Sets the role name.
     *
     * @param string $name The role name
     *
     * @return self
     */
    public function setName($name);

    /**
     * Gets the role name.
     *
     * @return string the role name
     */
    public function getName();

    /**
     * @return string
     */
    public function __toString();
}
