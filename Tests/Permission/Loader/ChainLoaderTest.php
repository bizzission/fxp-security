<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Permission\Loader;

use Fxp\Component\Security\Exception\LoaderException;
use Fxp\Component\Security\Permission\Loader\ChainLoader;
use Fxp\Component\Security\Permission\Loader\LoaderInterface;
use Fxp\Component\Security\Permission\PermissionConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ChainLoaderTest extends TestCase
{
    public function testWithoutLoaders(): void
    {
        $loader = new ChainLoader([]);

        $this->assertSame([], $loader->loadConfigurations());
    }

    public function testWithLoaders(): void
    {
        $loader1Configs = [
            $this->getMockBuilder(PermissionConfigInterface::class)->getMock(),
        ];
        $loader2Configs = [
            $this->getMockBuilder(PermissionConfigInterface::class)->getMock(),
        ];

        $expectedConfigs = array_merge($loader1Configs, $loader2Configs);

        $loader1 = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $loader1->expects($this->once())
            ->method('loadConfigurations')
            ->willReturn($loader1Configs)
        ;

        $loader2 = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $loader2->expects($this->once())
            ->method('loadConfigurations')
            ->willReturn($loader2Configs)
        ;

        $loader = new ChainLoader([$loader1, $loader2]);

        $this->assertSame($expectedConfigs, $loader->loadConfigurations());
    }

    public function testWithInvalidLoader(): void
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage('Class stdClass is expected to implement LoaderInterface');

        new ChainLoader([
            new \stdClass(),
        ]);
    }
}
