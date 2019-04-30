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

use Fxp\Component\Security\Permission\PermissionFieldConfig;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 * @coversNothing
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
}
