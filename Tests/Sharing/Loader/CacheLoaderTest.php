<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Sharing\Loader;

use Fxp\Component\Security\Sharing\Loader\CacheLoader;
use Fxp\Component\Security\Sharing\Loader\LoaderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class CacheLoaderTest extends TestCase
{
    /**
     * @var LoaderInterface|MockObject
     */
    private $loader;

    /**
     * @var ConfigCacheFactoryInterface|MockObject
     */
    private $configCacheFactory;

    /**
     * @var string
     */
    private $cacheDir;

    protected function setUp(): void
    {
        $this->loader = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $this->configCacheFactory = $this->getMockBuilder(ConfigCacheFactoryInterface::class)->getMock();
        $this->cacheDir = sys_get_temp_dir().uniqid('/fxp_security_', true);
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $fs->remove($this->cacheDir);

        $this->loader = null;
        $this->configCacheFactory = null;
        $this->cacheDir = null;
    }

    public function testLoadConfigsWithoutCacheDir(): void
    {
        $cacheLoader = new CacheLoader($this->loader, [
            'cache_dir' => null,
        ]);
        $cacheLoader->setConfigCacheFactory($this->configCacheFactory);

        $this->loader->expects($this->once())
            ->method('loadSubjectConfigurations')
        ;

        $this->loader->expects($this->once())
            ->method('loadIdentityConfigurations')
        ;

        $this->configCacheFactory->expects($this->never())
            ->method('cache')
        ;

        $cacheLoader->loadSubjectConfigurations();
        $cacheLoader->loadIdentityConfigurations();

        // Test the execution cache
        $cacheLoader->loadSubjectConfigurations();
        $cacheLoader->loadIdentityConfigurations();
    }

    public function testLoadConfigsWithDebug(): void
    {
        $cacheLoader = new CacheLoader($this->loader, [
            'debug' => true,
        ]);
        $cacheLoader->setConfigCacheFactory($this->configCacheFactory);

        $this->loader->expects($this->once())
            ->method('loadSubjectConfigurations')
        ;

        $this->loader->expects($this->once())
            ->method('loadIdentityConfigurations')
        ;

        $this->configCacheFactory->expects($this->never())
            ->method('cache')
        ;

        $cacheLoader->loadSubjectConfigurations();
        $cacheLoader->loadIdentityConfigurations();

        // Test the execution cache
        $cacheLoader->loadSubjectConfigurations();
        $cacheLoader->loadIdentityConfigurations();
    }

    public function testLoadConfigsWithCacheDir(): void
    {
        $fs = new Filesystem();

        $cacheFileSubjects = $this->cacheDir.'/cache_file_subjects.php';
        $fs->dumpFile($cacheFileSubjects, '<?php'.PHP_EOL.'    return [];'.PHP_EOL);

        $cacheFileIdentities = $this->cacheDir.'/cache_file_identities.php';
        $fs->dumpFile($cacheFileIdentities, '<?php'.PHP_EOL.'    return [];'.PHP_EOL);

        $cacheLoader = new CacheLoader($this->loader, [
            'cache_dir' => $this->cacheDir,
        ]);
        $cacheLoader->setConfigCacheFactory($this->configCacheFactory);

        $this->loader->expects($this->once())
            ->method('loadSubjectConfigurations')
        ;

        $this->loader->expects($this->once())
            ->method('loadIdentityConfigurations')
        ;

        $cache = $this->getMockBuilder(ConfigCacheInterface::class)->getMock();
        $cache->expects($this->at(0))
            ->method('write')
        ;
        $cache->expects($this->at(1))
            ->method('getPath')
            ->willReturn($cacheFileSubjects)
        ;
        $cache->expects($this->at(2))
            ->method('write')
        ;
        $cache->expects($this->at(3))
            ->method('getPath')
            ->willReturn($cacheFileIdentities)
        ;

        $this->configCacheFactory->expects($this->atLeastOnce())
            ->method('cache')
            ->willReturnCallback(static function ($file, $callable) use ($cache) {
                $callable($cache);

                return $cache;
            })
        ;

        $cacheLoader->loadSubjectConfigurations();
        $cacheLoader->loadIdentityConfigurations();

        // Test the execution cache
        $cacheLoader->loadSubjectConfigurations();
        $cacheLoader->loadIdentityConfigurations();
    }

    public function testWarmUpWithoutCacheDir(): void
    {
        $cacheLoader = new CacheLoader($this->loader, [
            'cache_dir' => null,
        ]);
        $cacheLoader->setConfigCacheFactory($this->configCacheFactory);

        $this->loader->expects($this->never())
            ->method('loadSubjectConfigurations')
        ;

        $this->loader->expects($this->never())
            ->method('loadIdentityConfigurations')
        ;

        $cacheLoader->warmUp('cache_dir');
    }

    public function testWarmUpWithCacheDir(): void
    {
        $cacheLoader = new CacheLoader($this->loader, [
            'cache_dir' => $this->cacheDir,
            'debug' => true,
        ]);
        $cacheLoader->setConfigCacheFactory($this->configCacheFactory);

        $this->loader->expects($this->once())
            ->method('loadSubjectConfigurations')
        ;

        $this->loader->expects($this->once())
            ->method('loadIdentityConfigurations')
        ;

        $cacheLoader->warmUp('cache_dir');
    }
}
