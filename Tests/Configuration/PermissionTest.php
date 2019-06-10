<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Configuration;

use Fxp\Component\Security\Configuration\Permission;
use Fxp\Component\Security\Configuration\PermissionField;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class PermissionTest extends TestCase
{
    public function testConstructor(): void
    {
        $field = new PermissionField();

        $config = new Permission([
            'operations' => ['read'],
            'mappingPermissions' => ['update' => 'edit'],
            'fields' => [
                'foo' => $field,
            ],
            'master' => 'foo',
            'masterFieldMappingPermissions' => ['view' => 'read'],
            'buildFields' => false,
            'buildDefaultFields' => false,
        ]);

        $this->assertSame(['read'], $config->getOperations());
        $this->assertSame(['update' => 'edit'], $config->getMappingPermissions());
        $this->assertSame(['foo' => $field], $config->getFields());
        $this->assertSame('foo', $config->getMaster());
        $this->assertSame(['view' => 'read'], $config->getMasterFieldMappingPermissions());
        $this->assertFalse($config->getBuildFields());
        $this->assertFalse($config->getBuildDefaultFields());
    }
}
