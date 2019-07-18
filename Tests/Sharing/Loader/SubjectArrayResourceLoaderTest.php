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

use Fxp\Component\Config\ArrayResource;
use Fxp\Component\Security\Sharing\Loader\SubjectArrayResourceLoader;
use Fxp\Component\Security\Sharing\SharingSubjectConfigCollection;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SubjectArrayResourceLoaderTest extends TestCase
{
    public function testSupports(): void
    {
        $loader = new SubjectArrayResourceLoader();

        static::assertTrue($loader->supports(new ArrayResource()));
        static::assertTrue($loader->supports(new ArrayResource(), 'foo'));
        static::assertFalse($loader->supports(new \stdClass()));
    }

    /**
     * @throws
     */
    public function testLoad(): void
    {
        $resource = new ArrayResource();
        $loader = new SubjectArrayResourceLoader();

        $configs = $loader->load($resource);

        static::assertInstanceOf(SharingSubjectConfigCollection::class, $configs);
        static::assertCount(0, $configs);
    }
}
