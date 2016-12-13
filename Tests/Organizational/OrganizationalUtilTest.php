<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Organizational;

use Sonatra\Component\Security\Organizational\OrganizationalUtil;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockOrganization;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockUserOrganizationUsers;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrganizationalUtilTest extends \PHPUnit_Framework_TestCase
{
    public function testFormatName()
    {
        $object = new MockObject('foo');
        $res = OrganizationalUtil::formatName($object, 'ROLE_TEST');

        $this->assertSame('ROLE_TEST', $res);
    }

    public function testFormatNameWithOrganization()
    {
        $object = new MockUserOrganizationUsers();
        $object->setOrganization(new MockOrganization('foo'));
        $res = OrganizationalUtil::formatName($object, 'ROLE_TEST');

        $this->assertSame('ROLE_TEST__foo', $res);
    }

    public function testGeneric()
    {
        $res = OrganizationalUtil::formatGeneric('ROLE_TEST');

        $this->assertSame('ROLE_TEST', $res);
    }

    public function testGenericWithOrganization()
    {
        $res = OrganizationalUtil::formatGeneric('ROLE_TEST__foo');

        $this->assertSame('ROLE_TEST__org', $res);
    }

    public function testFormat()
    {
        $res = OrganizationalUtil::format('ROLE_TEST');

        $this->assertSame('ROLE_TEST', $res);
    }

    public function testFormatWithOrganization()
    {
        $res = OrganizationalUtil::format('ROLE_TEST__foo');

        $this->assertSame('ROLE_TEST', $res);
    }
}
