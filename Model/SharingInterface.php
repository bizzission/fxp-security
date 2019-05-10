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
use Fxp\Component\Security\Model\Traits\RoleableInterface;

/**
 * Sharing interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface SharingInterface extends PermissionsInterface, RoleableInterface
{
    /**
     * Get the id.
     *
     * @return null|int|string
     */
    public function getId();

    /**
     * Set the classname of subject.
     *
     * @param null|string $class The classname
     *
     * @return static
     */
    public function setSubjectClass(?string $class);

    /**
     * Get the classname of subject.
     *
     * @return null|string
     */
    public function getSubjectClass(): ?string;

    /**
     * Set the id of subject.
     *
     * @param int|string $id The id
     *
     * @return static
     */
    public function setSubjectId($id);

    /**
     * Get the id of subject.
     *
     * @return int|string
     */
    public function getSubjectId();

    /**
     * Set the classname of identity.
     *
     * @param null|string $class The classname
     *
     * @return static
     */
    public function setIdentityClass(?string $class);

    /**
     * Get the classname of identity.
     *
     * @return null|string
     */
    public function getIdentityClass(): ?string;

    /**
     * Set the unique name of identity.
     *
     * @param null|int|string $name The unique name
     *
     * @return static
     */
    public function setIdentityName($name);

    /**
     * Get the unique name of identity.
     *
     * @return null|int|string
     */
    public function getIdentityName();

    /**
     * Define if the sharing entry is enabled.
     *
     * @param bool $enabled The value
     *
     * @return static
     */
    public function setEnabled(bool $enabled);

    /**
     * Check if the sharing entry is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Set the date when the sharing entry must start.
     *
     * @param null|\DateTime $date The date
     *
     * @return static
     */
    public function setStartedAt(?\DateTime $date);

    /**
     * Get the date when the sharing entry must start.
     *
     * @return null|\DateTime
     */
    public function getStartedAt(): ?\DateTime;

    /**
     * Set the date when the sharing entry must end.
     *
     * @param null|\DateTime $date The date
     *
     * @return static
     */
    public function setEndedAt(?\DateTime $date);

    /**
     * Get the date when the sharing entry must end.
     *
     * @return null|\DateTime
     */
    public function getEndedAt(): ?\DateTime;
}
