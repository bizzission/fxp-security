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
use Sonatra\Component\Security\Permission\PermissionFieldConfig;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testPermissionConfigByDefault()
    {
        $operations = array('create', 'view', 'update', 'delete');
        $config = new PermissionConfig(MockObject::class, $operations);

        $this->assertSame(MockObject::class, $config->getType());
        $this->assertSame(array(), $config->getFields());
        $this->assertNull($config->getMaster());
    }

    public function testPermissionConfig()
    {
        $operations = array('invite', 'view', 'update', 'revoke');
        $alias = array(
            'create' => 'invite',
            'delete' => 'revoke',
        );
        $fields = array(
            'name' => new PermissionFieldConfig('name'),
        );
        $master = 'foo';
        $config = new PermissionConfig(MockObject::class, $operations, $alias, array_values($fields), $master);

        $this->assertSame(MockObject::class, $config->getType());

        $this->assertSame($fields, $config->getFields());
        $this->assertSame($fields['name'], $config->getField('name'));
        $this->assertNull($config->getField('foo'));

        $this->assertSame($master, $config->getMaster());

        $this->assertSame($operations, $config->getOperations());
        $this->assertTrue($config->hasOperation('view'));
        $this->assertFalse($config->hasOperation('foo'));
        $this->assertSame($alias, $config->getMappingPermissions());
        $this->assertTrue($config->hasOperation('create'));
    }
}
