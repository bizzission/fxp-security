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

use Fxp\Component\Security\Exception\LoaderException;
use Fxp\Component\Security\Sharing\Loader\ChainLoader;
use Fxp\Component\Security\Sharing\Loader\LoaderInterface;
use Fxp\Component\Security\Sharing\SharingIdentityConfigInterface;
use Fxp\Component\Security\Sharing\SharingSubjectConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ChainLoaderTest extends TestCase
{
    public function testWithoutLoaders(): void
    {
        $loader = new ChainLoader([]);

        $this->assertSame([], $loader->loadSubjectConfigurations());
        $this->assertSame([], $loader->loadIdentityConfigurations());
    }

    public function testWithLoaders(): void
    {
        $loader1Subjects = [
            $this->getMockBuilder(SharingSubjectConfigInterface::class)->getMock(),
        ];
        $loader2Subjects = [
            $this->getMockBuilder(SharingSubjectConfigInterface::class)->getMock(),
        ];
        $loader1Identities = [
            $this->getMockBuilder(SharingIdentityConfigInterface::class)->getMock(),
        ];
        $loader2Identities = [
            $this->getMockBuilder(SharingIdentityConfigInterface::class)->getMock(),
        ];

        $expectedSubjects = array_merge($loader1Subjects, $loader2Subjects);
        $expectedIdentities = array_merge($loader1Identities, $loader2Identities);

        $loader1 = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $loader1->expects($this->once())
            ->method('loadSubjectConfigurations')
            ->willReturn($loader1Subjects)
        ;
        $loader1->expects($this->once())
            ->method('loadIdentityConfigurations')
            ->willReturn($loader1Identities)
        ;

        $loader2 = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $loader2->expects($this->once())
            ->method('loadSubjectConfigurations')
            ->willReturn($loader2Subjects)
        ;
        $loader2->expects($this->once())
            ->method('loadIdentityConfigurations')
            ->willReturn($loader2Identities)
        ;

        $loader = new ChainLoader([$loader1, $loader2]);

        $this->assertSame($expectedSubjects, $loader->loadSubjectConfigurations());
        $this->assertSame($expectedIdentities, $loader->loadIdentityConfigurations());
    }

    public function testWithInvalidLoader(): void
    {
        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage('Class stdClass is expected to implement LoaderInterface');

        new ChainLoader([
            new \stdClass(),
        ]);
    }
}
