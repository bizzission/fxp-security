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
 * Trait of add dependency entity with an optional user.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
trait OwnerableOptionalTrait
{
    /**
     * @var UserInterface|null
     */
    protected $owner;

    /**
     * {@inheritdoc}
     */
    public function setOwner($user)
    {
        $this->owner = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnerId()
    {
        return null !== $this->getOwner()
            ? $this->getOwner()->getId()
            : null;
    }
}
