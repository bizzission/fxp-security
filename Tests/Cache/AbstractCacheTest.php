<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Cache;

use Fxp\Component\Security\Tests\Fixtures\Cache\MockCache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\ConfigCacheFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class AbstractCacheTest extends TestCase
{
    /**
     * @throws
     */
    public function testWarmUp(): void
    {
        /** @var ConfigCacheFactoryInterface|MockObject $configCacheFactory */
        $configCacheFactory = $this->getMockBuilder(ConfigCacheFactoryInterface::class)->getMock();
        $cacheLoader = new MockCache([
            'project_dir' => \dirname(__DIR__),
            'resource_prefixes' => ['Cache'],
        ]);

        $this->assertNull($cacheLoader->getProtectedConfigCacheFactory());
        $cacheLoader->warmUp('cache_dir');
        $this->assertNotNull($cacheLoader->getProtectedConfigCacheFactory());

        $cacheLoader->setConfigCacheFactory($configCacheFactory);
        $this->assertSame($configCacheFactory, $cacheLoader->getProtectedConfigCacheFactory());
    }
}
