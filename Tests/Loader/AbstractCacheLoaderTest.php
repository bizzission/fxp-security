<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Loader;

use Fxp\Component\Security\Tests\Fixtures\Loader\MockCacheLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\ConfigCacheFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class AbstractCacheLoaderTest extends TestCase
{
    /**
     * @throws
     */
    public function testWarmUp(): void
    {
        /** @var ConfigCacheFactoryInterface|MockObject $configCacheFactory */
        $configCacheFactory = $this->getMockBuilder(ConfigCacheFactoryInterface::class)->getMock();
        $cacheLoader = new MockCacheLoader([
            'project_dir' => \dirname(__DIR__),
            'resource_prefixes' => ['Loader'],
        ]);

        $this->assertNull($cacheLoader->getProtectedConfigCacheFactory());
        $cacheLoader->warmUp('cache_dir');
        $this->assertNotNull($cacheLoader->getProtectedConfigCacheFactory());

        $cacheLoader->setConfigCacheFactory($configCacheFactory);
        $this->assertSame($configCacheFactory, $cacheLoader->getProtectedConfigCacheFactory());
    }
}
