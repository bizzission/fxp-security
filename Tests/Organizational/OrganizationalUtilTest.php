<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Organizational;

use Fxp\Component\Security\Organizational\OrganizationalUtil;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserOrganizationUsers;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class OrganizationalUtilTest extends TestCase
{
    public function testFormatName(): void
    {
        $object = new MockObject('foo');
        $res = OrganizationalUtil::formatName($object, 'ROLE_TEST');

        static::assertSame('ROLE_TEST', $res);
    }

    public function testFormatNameWithOrganization(): void
    {
        $object = new MockUserOrganizationUsers();
        $object->setOrganization(new MockOrganization('foo'));
        $res = OrganizationalUtil::formatName($object, 'ROLE_TEST');

        static::assertSame('ROLE_TEST__foo', $res);
    }

    public function testFormat(): void
    {
        $res = OrganizationalUtil::format('ROLE_TEST');

        static::assertSame('ROLE_TEST', $res);
    }

    public function testFormatWithOrganization(): void
    {
        $res = OrganizationalUtil::format('ROLE_TEST__foo');

        static::assertSame('ROLE_TEST', $res);
    }

    public function testGetSuffix(): void
    {
        $res = OrganizationalUtil::getSuffix('ROLE_TEST');

        static::assertSame('', $res);
    }

    public function testGetSuffixWithOrganization(): void
    {
        $res = OrganizationalUtil::getSuffix('ROLE_TEST__foo');

        static::assertSame('__foo', $res);
    }
}
