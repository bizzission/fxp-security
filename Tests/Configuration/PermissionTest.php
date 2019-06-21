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

        static::assertSame(['read'], $config->getOperations());
        static::assertSame(['update' => 'edit'], $config->getMappingPermissions());
        static::assertSame(['foo' => $field], $config->getFields());
        static::assertSame('foo', $config->getMaster());
        static::assertSame(['view' => 'read'], $config->getMasterFieldMappingPermissions());
        static::assertFalse($config->getBuildFields());
        static::assertFalse($config->getBuildDefaultFields());
    }
}
