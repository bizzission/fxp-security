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
use Fxp\Component\Security\Annotation\ClassFinder;
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

        $finder->expects($this->once())
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

        $this->assertCount(2, $configs);

        foreach ($configs as $config) {
            $this->assertInstanceOf(PermissionConfigInterface::class, $config);

            if (MockObjectWithAnnotation::class === $config->getType()) {
                $this->assertSame(MockObjectWithAnnotation::class, $config->getType());
                $this->assertSame(['view', 'create', 'update', 'delete'], $config->getOperations());
                $this->assertSame('foo', $config->getMaster());

                $this->assertCount(2, $config->getFields());
                $this->assertTrue($config->hasField('id'));
                $this->assertTrue($config->hasField('name'));

                $this->assertSame(['read', 'view'], $config->getField('id')->getOperations());
                $this->assertSame(['read', 'edit'], $config->getField('name')->getOperations());
            } elseif (MockObjectWithOnlyFieldAnnotation::class === $config->getType()) {
                $this->assertSame(MockObjectWithOnlyFieldAnnotation::class, $config->getType());
                $this->assertSame([], $config->getOperations());

                $this->assertCount(1, $config->getFields());
                $this->assertTrue($config->hasField('name'));

                $this->assertSame(['read', 'edit'], $config->getField('name')->getOperations());
            }
        }
    }
}
