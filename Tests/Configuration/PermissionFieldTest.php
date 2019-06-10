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

use Fxp\Component\Security\Configuration\PermissionField;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class PermissionFieldTest extends TestCase
{
    public function testConstructor(): void
    {
        $config = new PermissionField([
            'operations' => ['read'],
            'mappingPermissions' => ['update' => 'edit'],
            'editable' => true,
        ]);

        $this->assertSame(['read'], $config->getOperations());
        $this->assertSame(['update' => 'edit'], $config->getMappingPermissions());
        $this->assertTrue($config->getEditable());
    }
}
