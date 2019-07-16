<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Permission\Loader;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Fxp\Component\Config\Loader\ClassFinder;
use Fxp\Component\Security\Permission\Loader\AnnotationLoader;
use Fxp\Component\Security\Permission\PermissionConfigInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObjectWithAnnotation;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObjectWithOnlyFieldAnnotation;
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
    public function testLoadConfigurations(): void
    {
        /** @var ClassFinder|MockObject $finder */
        $finder = $this->getMockBuilder(ClassFinder::class)
            ->setMethods(['findClasses'])
            ->getMock()
        ;

        $finder->expects(static::once())
            ->method('findClasses')
            ->willReturn([
                MockObjectWithAnnotation::class,
                MockObjectWithOnlyFieldAnnotation::class,
                'InvalidClass',
            ])
        ;

        $reader = new AnnotationReader();
        $loader = new AnnotationLoader($reader, $finder);
        /** @var PermissionConfigInterface[] $configs */
        $configs = $loader->loadConfigurations();

        static::assertCount(2, $configs);

        foreach ($configs as $config) {
            static::assertInstanceOf(PermissionConfigInterface::class, $config);

            if (MockObjectWithAnnotation::class === $config->getType()) {
                static::assertSame(MockObjectWithAnnotation::class, $config->getType());
                static::assertSame(['view', 'create', 'update', 'delete'], $config->getOperations());
                static::assertSame('foo', $config->getMaster());

                static::assertCount(2, $config->getFields());
                static::assertTrue($config->hasField('id'));
                static::assertTrue($config->hasField('name'));

                static::assertSame(['read', 'view'], $config->getField('id')->getOperations());
                static::assertSame(['read', 'edit'], $config->getField('name')->getOperations());
            } elseif (MockObjectWithOnlyFieldAnnotation::class === $config->getType()) {
                static::assertSame(MockObjectWithOnlyFieldAnnotation::class, $config->getType());
                static::assertSame([], $config->getOperations());

                static::assertCount(1, $config->getFields());
                static::assertTrue($config->hasField('name'));

                static::assertSame(['read', 'edit'], $config->getField('name')->getOperations());
            }
        }
    }
}
