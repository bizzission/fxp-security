<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Annotation;

use Fxp\Component\Security\Annotation\ClassFinder;
use Fxp\Component\Security\Tests\Fixtures\Token\MockToken;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ClassFinderTest extends TestCase
{
    public function testFindClasses(): void
    {
        $finder = new ClassFinder(
            [
                \dirname(__DIR__).'/Fixtures',
            ],
            [
                'Cache',
                'Listener',
                'Model',
            ]
        );

        $expected = [
            MockToken::class,
        ];

        static::assertSame($expected, $finder->findClasses());
    }
}
