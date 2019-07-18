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

use Fxp\Component\Security\Sharing\Loader\SubjectConfigurationLoader;
use Fxp\Component\Security\Sharing\SharingSubjectConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SubjectConfigurationLoaderTest extends TestCase
{
    public function testSupports(): void
    {
        $loader = new SubjectConfigurationLoader();

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
        $config = $this->getMockBuilder(SharingSubjectConfigInterface::class)->getMock();
        $loader = new SubjectConfigurationLoader([$config]);

        $configs = $loader->load('.', 'config');

        static::assertCount(1, $configs);
        static::assertSame($config, current($configs->all()));
    }
}
