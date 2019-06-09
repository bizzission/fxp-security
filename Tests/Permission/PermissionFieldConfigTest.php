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
use Fxp\Component\Security\Permission\PermissionFieldConfig;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class PermissionFieldConfigTest extends TestCase
{
    public function testPermissionFieldConfigByDefault(): void
    {
        $config = new PermissionFieldConfig('foo');

        $this->assertSame('foo', $config->getField());
        $this->assertSame([], $config->getOperations());
        $this->assertFalse($config->hasOperation('foo'));
        $this->assertTrue($config->isEditable());
    }

    public function testPermissionFieldConfig(): void
    {
        $operations = ['read', 'edit'];
        $alias = [
            'test' => 'read',
        ];
        $config = new PermissionFieldConfig('foo', $operations, $alias);

        $this->assertSame('foo', $config->getField());
        $this->assertSame($operations, $config->getOperations());
        $this->assertTrue($config->hasOperation('read'));
        $this->assertFalse($config->hasOperation('foo'));
        $this->assertSame($alias, $config->getMappingPermissions());
        $this->assertTrue($config->hasOperation('test'));
        $this->assertFalse($config->isEditable());
    }

    public function testMerge(): void
    {
        $config = new PermissionFieldConfig('foo', ['read'], ['update' => 'edit'], false);

        $this->assertSame('foo', $config->getField());
        $this->assertSame(['read'], $config->getOperations());
        $this->assertSame(['update' => 'edit'], $config->getMappingPermissions());
        $this->assertFalse($config->isEditable());

        $config->merge(new PermissionFieldConfig('foo', ['update'], ['view' => 'read'], true));

        $this->assertSame('foo', $config->getField());
        $this->assertSame(['read', 'update'], $config->getOperations());
        $this->assertSame(['update' => 'edit', 'view' => 'read'], $config->getMappingPermissions());
        $this->assertTrue($config->isEditable());
    }

    public function testMergeWithInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The permission field config of "foo" can be merged only with the same field, given: "bar"');

        $config = new PermissionFieldConfig('foo');

        $config->merge(new PermissionFieldConfig('bar'));
    }
}
