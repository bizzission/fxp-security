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

use Fxp\Component\Security\Permission\PermissionFactoryCacheWarmer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class PermissionFactoryCacheWarmerTest extends TestCase
{
    public function testWarmUp(): void
    {
        $cacheLoader = $this->getMockBuilder(WarmableInterface::class)->getMock();
        $cacheLoader->expects(static::once())
            ->method('warmUp')
            ->with('cache_dir')
        ;

        /** @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->expects(static::once())
            ->method('get')
            ->with('fxp_security.permission_factory')
            ->willReturn($cacheLoader)
        ;

        $warmer = new PermissionFactoryCacheWarmer($container);
        static::assertTrue($warmer->isOptional());

        $warmer->warmUp('cache_dir');
    }
}
