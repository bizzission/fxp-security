<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Permission;

use Sonatra\Component\Security\Permission\PermissionConfig;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testPermissionConfigByDefault()
    {
        $config = new PermissionConfig(MockObject::class);

        $this->assertSame(MockObject::class, $config->getType());
        $this->assertSame(array(), $config->getFields());
        $this->assertNull($config->getMaster());
    }
}
