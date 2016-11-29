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

use Sonatra\Component\Security\Sharing\SharingSubjectConfig;
use Sonatra\Component\Security\SharingVisibilities;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingSubjectConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testSharingSubjectConfigByDefault()
    {
        $config = new SharingSubjectConfig(MockObject::class);

        $this->assertSame(MockObject::class, $config->getType());
        $this->assertSame(SharingVisibilities::TYPE_NONE, $config->getVisibility());
    }

    public function testSharingSubjectConfig()
    {
        $config = new SharingSubjectConfig(MockObject::class, SharingVisibilities::TYPE_PRIVATE);

        $this->assertSame(MockObject::class, $config->getType());
        $this->assertSame(SharingVisibilities::TYPE_PRIVATE, $config->getVisibility());
    }
}
