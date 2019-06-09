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

use Fxp\Component\Security\Permission\Loader\LoaderInterface;
use Fxp\Component\Security\Permission\PermissionConfigInterface;
use Fxp\Component\Security\Permission\PermissionFactory;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject as FixtureMockObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class PermissionFactoryTest extends TestCase
{
    /**
     * @var LoaderInterface|MockObject
     */
    private $loader;

    /**
     * @var PermissionFactory
     */
    private $factory;

    protected function setUp(): void
    {
        $this->loader = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $this->factory = new PermissionFactory($this->loader);
    }

    protected function tearDown(): void
    {
        $this->loader = null;
        $this->factory = null;
    }

    public function testCreateConfigurations(): void
    {
        $config1 = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();
        $config1->expects($this->atLeast(2))
            ->method('getType')
            ->willReturn(FixtureMockObject::class)
        ;

        $config2 = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();
        $config2->expects($this->atLeast(1))
            ->method('getType')
            ->willReturn(FixtureMockObject::class)
        ;

        $config1->expects($this->atLeast(1))
            ->method('merge')
            ->with($config2)
        ;

        $this->loader->expects($this->once())
            ->method('loadConfigurations')
            ->willReturn([$config1, $config2])
        ;

        $this->assertSame([FixtureMockObject::class => $config1], $this->factory->createConfigurations());
    }

    public function testCreateConfigurationsWithDefaultFields(): void
    {
        $this->factory = new PermissionFactory($this->loader, [
            'fields' => [
                'id' => [
                    'operations' => ['read'],
                ],
            ],
        ]);

        $config = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();
        $config->expects($this->atLeast(1))
            ->method('getType')
            ->willReturn(FixtureMockObject::class)
        ;
        $config->expects($this->atLeast(1))
            ->method('buildFields')
            ->willReturn(true)
        ;
        $config->expects($this->atLeast(1))
            ->method('buildDefaultFields')
            ->willReturn(true)
        ;

        $this->loader->expects($this->once())
            ->method('loadConfigurations')
            ->willReturn([$config])
        ;

        $config->expects($this->once())
            ->method('merge')
        ;

        $this->assertSame([FixtureMockObject::class => $config], $this->factory->createConfigurations());
    }

    public function testCreateConfigurationsWithDefaultMasterFieldMapping(): void
    {
        $this->factory = new PermissionFactory($this->loader, [
            'master_mapping_permissions' => [
                'view' => 'read',
                'update' => 'edit',
                'create' => 'edit',
                'delete' => 'edit',
            ],
        ]);

        $config = $this->getMockBuilder(PermissionConfigInterface::class)->getMock();
        $config->expects($this->atLeast(1))
            ->method('getType')
            ->willReturn(FixtureMockObject::class)
        ;
        $config->expects($this->atLeast(1))
            ->method('getMaster')
            ->willReturn('foo')
        ;
        $config->expects($this->atLeast(1))
            ->method('getMasterFieldMappingPermissions')
            ->willReturn([])
        ;
        $config->expects($this->atLeast(1))
            ->method('buildFields')
            ->willReturn(true)
        ;
        $config->expects($this->atLeast(1))
            ->method('buildDefaultFields')
            ->willReturn(true)
        ;

        $this->loader->expects($this->once())
            ->method('loadConfigurations')
            ->willReturn([$config])
        ;

        $config->expects($this->atLeast(2))
            ->method('merge')
        ;

        $this->assertSame([FixtureMockObject::class => $config], $this->factory->createConfigurations());
    }
}
