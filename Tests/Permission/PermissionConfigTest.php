<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Permission;

use Fxp\Component\Security\Exception\InvalidArgumentException;
use Fxp\Component\Security\Permission\PermissionConfig;
use Fxp\Component\Security\Permission\PermissionFieldConfig;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class PermissionConfigTest extends TestCase
{
    public function testPermissionConfigByDefault(): void
    {
        $operations = ['create', 'view', 'update', 'delete'];
        $config = new PermissionConfig(MockObject::class, $operations);

        $this->assertSame(MockObject::class, $config->getType());
        $this->assertSame([], $config->getFields());
        $this->assertNull($config->getMaster());
    }

    public function testPermissionConfig(): void
    {
        $operations = ['invite', 'view', 'update', 'revoke'];
        $alias = [
            'create' => 'invite',
            'delete' => 'revoke',
        ];
        $fields = [
            'name' => new PermissionFieldConfig('name'),
        ];
        $master = 'foo';
        $masterMapping = [
            'view' => 'read',
        ];
        $config = new PermissionConfig(
            MockObject::class,
            $operations,
            $alias,
            array_values($fields),
            $master,
            $masterMapping,
            false,
            false
        );

        $this->assertSame(MockObject::class, $config->getType());

        $this->assertSame($fields, $config->getFields());
        $this->assertSame($fields['name'], $config->getField('name'));
        $this->assertNull($config->getField('foo'));

        $this->assertSame($master, $config->getMaster());
        $this->assertSame($masterMapping, $config->getMasterFieldMappingPermissions());
        $this->assertFalse($config->buildFields());
        $this->assertFalse($config->buildDefaultFields());

        $this->assertSame($operations, $config->getOperations());
        $this->assertTrue($config->hasOperation('view'));
        $this->assertFalse($config->hasOperation('foo'));
        $this->assertSame($alias, $config->getMappingPermissions());
        $this->assertTrue($config->hasOperation('create'));
    }

    public function testMerge(): void
    {
        $nameField = new PermissionFieldConfig('name');
        $idField = new PermissionFieldConfig('id');

        $config = new PermissionConfig(
            MockObject::class,
            ['invite', 'view', 'update', 'revoke'],
            [
                'create' => 'invite',
                'delete' => 'revoke',
            ],
            [
                $nameField,
            ],
            'foo',
            [
                'view' => 'read',
            ]
        );

        $this->assertSame(MockObject::class, $config->getType());
        $this->assertSame(['name' => $nameField], $config->getFields());
        $this->assertSame(['invite', 'view', 'update', 'revoke'], $config->getOperations());
        $this->assertSame(['create' => 'invite', 'delete' => 'revoke'], $config->getMappingPermissions());
        $this->assertSame('foo', $config->getMaster());
        $this->assertSame(['view' => 'read'], $config->getMasterFieldMappingPermissions());
        $this->assertTrue($config->buildFields());
        $this->assertTrue($config->buildDefaultFields());

        $config->merge(new PermissionConfig(
            MockObject::class,
            ['delete'],
            [
                'view' => 'read',
            ],
            [
                'id' => $idField, 'name' => new PermissionFieldConfig('name'),
            ],
            'foo',
            [
                'create' => 'edit',
            ],
            false,
            false
        ));

        $this->assertSame(MockObject::class, $config->getType());
        $this->assertSame(['id' => $idField, 'name' => $nameField], $config->getFields());
        $this->assertSame(['invite', 'view', 'update', 'revoke', 'delete'], $config->getOperations());
        $this->assertSame(['create' => 'invite', 'delete' => 'revoke', 'view' => 'read'], $config->getMappingPermissions());
        $this->assertSame('foo', $config->getMaster());
        $this->assertSame(['view' => 'read', 'create' => 'edit'], $config->getMasterFieldMappingPermissions());
        $this->assertFalse($config->buildFields());
        $this->assertFalse($config->buildDefaultFields());
    }

    public function testMergeWithInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The permission config of "Fxp\Component\Security\Tests\Fixtures\Model\MockObject" can be merged only with the same type, given: "stdClass"');

        $config = new PermissionConfig(MockObject::class);

        $config->merge(new PermissionConfig(\stdClass::class));
    }
}
