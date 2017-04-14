<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Exception;

use Sonatra\Component\Security\Exception\PermissionNotFoundException;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $expected = 'The permission "read" for "stdClass" is not found ant it required by the permission configuration';
        $e = new PermissionNotFoundException('read', \stdClass::class);

        $this->assertSame($expected, $e->getMessage());
    }

    public function testExceptionWithField()
    {
        $expected = 'The permission "read" for "stdClass::foo" is not found ant it required by the permission configuration';
        $e = new PermissionNotFoundException('read', \stdClass::class, 'foo');

        $this->assertSame($expected, $e->getMessage());
    }
}
