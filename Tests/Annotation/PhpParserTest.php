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

use Fxp\Component\Security\Annotation\PhpParser;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class PhpParserTest extends TestCase
{
    public function testExtractClasses(): void
    {
        $ref = new \ReflectionClass(MockObject::class);

        $classes = PhpParser::extractClasses($ref->getFileName());
        $expected = [
            MockObject::class,
        ];

        static::assertSame($expected, $classes);
    }
}
