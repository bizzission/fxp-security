<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Sharing;

use Fxp\Component\Security\Sharing\Loader\LoaderInterface;
use Fxp\Component\Security\Sharing\SharingFactory;
use Fxp\Component\Security\Sharing\SharingIdentityConfigInterface;
use Fxp\Component\Security\Sharing\SharingSubjectConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SharingFactoryTest extends TestCase
{
    public function getConfigTypes(): array
    {
        return [
            [SharingSubjectConfigInterface::class, 'loadSubjectConfigurations', 'createSubjectConfigurations'],
            [SharingIdentityConfigInterface::class, 'loadIdentityConfigurations', 'createIdentityConfigurations'],
        ];
    }

    /**
     * @dataProvider getConfigTypes
     *
     * @param string $configInterface
     * @param string $loadMethod
     * @param string $createMethod
     */
    public function testCreateConfigurations($configInterface, $loadMethod, $createMethod): void
    {
        $config1 = $this->getMockBuilder($configInterface)->getMock();
        $config1->expects(static::atLeast(2))
            ->method('getType')
            ->willReturn(MockObject::class)
        ;

        $config2 = $this->getMockBuilder($configInterface)->getMock();
        $config2->expects(static::atLeast(1))
            ->method('getType')
            ->willReturn(MockObject::class)
        ;

        $config1->expects(static::atLeast(1))
            ->method('merge')
            ->with($config2)
        ;

        /** @var LoaderInterface|MockObject $loader */
        $loader = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $loader->expects(static::once())
            ->method($loadMethod)
            ->willReturn([$config1, $config2])
        ;

        $factory = new SharingFactory($loader);

        static::assertSame([MockObject::class => $config1], $factory->{$createMethod}());
    }
}
