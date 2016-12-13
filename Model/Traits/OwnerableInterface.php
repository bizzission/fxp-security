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

use Sonatra\Component\Security\Model\UserInterface;

/**
 * Interface of add dependency entity with an user.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface OwnerableInterface
{
    /**
     * Set the owner.
     *
     * @param UserInterface $user The user
     *
     * @return self
     */
    public function setOwner(UserInterface $user);

    /**
     * Get the owner.
     *
     * @return UserInterface|null
     */
    public function getOwner();

    /**
     * Get the owner id.
     *
     * @return int|string|null
     */
    public function getOwnerId();
}
