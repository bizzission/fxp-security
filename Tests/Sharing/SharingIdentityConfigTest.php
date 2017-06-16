<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Sharing;

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Security\Sharing\SharingIdentityConfig;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingIdentityConfigTest extends TestCase
{
    public function testSharingIdentityConfigByDefault()
    {
        $config = new SharingIdentityConfig(MockObject::class);

        $this->assertSame(MockObject::class, $config->getType());
        $this->assertSame('mockobject', $config->getAlias());
        $this->assertFalse($config->isRoleable());
        $this->assertFalse($config->isPermissible());
    }

    public function testSharingIdentityConfig()
    {
        $config = new SharingIdentityConfig(MockObject::class, 'mock_object', true, true);

        $this->assertSame(MockObject::class, $config->getType());
        $this->assertSame('mock_object', $config->getAlias());
        $this->assertTrue($config->isRoleable());
        $this->assertTrue($config->isPermissible());
    }
}
