<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Sharing;

use Fxp\Component\Security\Sharing\SharingFactoryCacheWarmer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SharingFactoryCacheWarmerTest extends TestCase
{
    public function testWarmUp(): void
    {
        $cacheLoader = $this->getMockBuilder(WarmableInterface::class)->getMock();
        $cacheLoader->expects($this->once())
            ->method('warmUp')
            ->with('cache_dir')
        ;

        /** @var ContainerInterface|MockObject $container */
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $container->expects($this->once())
            ->method('get')
            ->with('fxp_security.sharing_factory')
            ->willReturn($cacheLoader)
        ;

        $warmer = new SharingFactoryCacheWarmer($container);
        $this->assertTrue($warmer->isOptional());

        $warmer->warmUp('cache_dir');
    }
}
