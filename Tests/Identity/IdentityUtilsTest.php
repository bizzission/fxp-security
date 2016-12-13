<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Identity;

use Sonatra\Component\Security\Identity\IdentityUtils;
use Sonatra\Component\Security\Identity\RoleSecurityIdentity;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class IdentityUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testMerge()
    {
        $role1 = new RoleSecurityIdentity('ROLE_USER');
        $role2 = new RoleSecurityIdentity('ROLE_ADMIN');
        $role3 = new RoleSecurityIdentity('ROLE_USER');
        $role4 = new RoleSecurityIdentity('ROLE_FOO');

        $sids = array($role1, $role2);
        $newSids = array($role3, $role4);
        $valid = array($role1, $role2, $role4);

        $sids = IdentityUtils::merge($sids, $newSids);

        $this->assertEquals($valid, $sids);
    }
}
