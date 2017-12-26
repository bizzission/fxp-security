<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Fixtures\Model;

use Sonatra\Component\Security\Model\Traits\GroupableInterface;
use Sonatra\Component\Security\Model\UserInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MockUserGroupable implements UserInterface, GroupableInterface
{
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
    public function isAccountNonExpired()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true;
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
    public function eraseCredentials()
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return array('ROLE_TEST');
    }

    /**
     * {@inheritdoc}
     */
    public function hasGroup($name)
    {
        return 'GROUP_TEST' === $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups()
    {
        return array(
            new MockGroup('GROUP_TEST'),
        );
    }
}
