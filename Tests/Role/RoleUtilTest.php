<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Role;

use Fxp\Component\Security\Role\RoleUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 * @coversNothing
 */
final class RoleUtilTest extends TestCase
{
    public function testFormatRoles(): void
    {
        $value = [
            'ROLE_USER',
            'ROLE_TEST',
        ];
        $expected = [
            new Role('ROLE_USER'),
            new Role('ROLE_TEST'),
        ];

        $this->assertEquals($expected, RoleUtil::formatRoles($value, true));
    }

    public function testFormatRole(): void
    {
        $value = 'ROLE_TEST';
        $expected = new Role('ROLE_TEST');

        $this->assertEquals($expected, RoleUtil::formatRole($value, true));
    }
}
