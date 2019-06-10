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

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Fxp\Component\Security\Annotation\ClassFinder;
use Fxp\Component\Security\Sharing\Loader\AnnotationLoader;
use Fxp\Component\Security\Sharing\SharingIdentityConfigInterface;
use Fxp\Component\Security\Sharing\SharingSubjectConfigInterface;
use Fxp\Component\Security\SharingVisibilities;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObjectWithAnnotation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class AnnotationLoaderTest extends TestCase
{
    protected function setUp(): void
    {
        AnnotationRegistry::registerLoader('class_exists');
    }

    /**
     * @throws
     */
    public function testLoadSubjectConfigurations(): void
    {
        /** @var ClassFinder|MockObject $finder */
        $finder = $this->getMockBuilder(ClassFinder::class)
            ->setMethods(['findClasses'])
            ->getMock()
        ;

        $finder->expects($this->once())
            ->method('findClasses')
            ->willReturn([
                MockObjectWithAnnotation::class,
                'InvalidClass',
            ])
        ;

        $reader = new AnnotationReader();
        $loader = new AnnotationLoader($reader, $finder);
        /** @var SharingSubjectConfigInterface[] $configs */
        $configs = $loader->loadSubjectConfigurations();

        $this->assertCount(1, $configs);

        $config = current($configs);
        $this->assertSame(MockObjectWithAnnotation::class, $config->getType());
        $this->assertSame(SharingVisibilities::TYPE_PRIVATE, $config->getVisibility());
    }

    /**
     * @throws
     */
    public function testLoadIdentityConfigurations(): void
    {
        /** @var ClassFinder|MockObject $finder */
        $finder = $this->getMockBuilder(ClassFinder::class)
            ->setMethods(['findClasses'])
            ->getMock()
        ;

        $finder->expects($this->once())
            ->method('findClasses')
            ->willReturn([
                MockObjectWithAnnotation::class,
                'InvalidClass',
            ])
        ;

        $reader = new AnnotationReader();
        $loader = new AnnotationLoader($reader, $finder);
        /** @var SharingIdentityConfigInterface[] $configs */
        $configs = $loader->loadIdentityConfigurations();

        $this->assertCount(1, $configs);

        $config = current($configs);
        $this->assertSame(MockObjectWithAnnotation::class, $config->getType());
        $this->assertSame('object', $config->getAlias());
        $this->assertTrue($config->isRoleable());
        $this->assertTrue($config->isPermissible());
    }
}
