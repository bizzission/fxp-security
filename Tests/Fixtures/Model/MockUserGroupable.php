<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Fixtures\Model;

use Fxp\Component\Security\Model\Traits\EditGroupableInterface;
use Fxp\Component\Security\Model\Traits\EditGroupableTrait;
use Fxp\Component\Security\Model\Traits\RoleableTrait;
use Fxp\Component\Security\Model\UserInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MockUserGroupable implements UserInterface, EditGroupableInterface
{
    use RoleableTrait;
    use EditGroupableTrait;

    public function __construct($mockGroups = true)
    {
        if ($mockGroups) {
            $this->addGroup(new MockGroup('GROUP_TEST'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return 'password';
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return 'salt';
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return 'user.test';
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return ['ROLE_TEST'];
    }
}
