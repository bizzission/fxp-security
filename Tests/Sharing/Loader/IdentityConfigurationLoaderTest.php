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

use Fxp\Component\Security\Sharing\Loader\IdentityConfigurationLoader;
use Fxp\Component\Security\Sharing\SharingIdentityConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class IdentityConfigurationLoaderTest extends TestCase
{
    public function testSupports(): void
    {
        $loader = new IdentityConfigurationLoader();

        static::assertTrue($loader->supports('.', 'config'));
        static::assertTrue($loader->supports('', 'config'));
        static::assertTrue($loader->supports(null, 'config'));
        static::assertFalse($loader->supports('.', 'foo'));
        static::assertFalse($loader->supports(new \stdClass()));
    }

    /**
     * @throws
     */
    public function testLoad(): void
    {
        $config = $this->getMockBuilder(SharingIdentityConfigInterface::class)->getMock();
        $loader = new IdentityConfigurationLoader([$config]);

        $configs = $loader->load('.', 'config');

        static::assertCount(1, $configs);
        static::assertSame($config, current($configs->all()));
    }
}
