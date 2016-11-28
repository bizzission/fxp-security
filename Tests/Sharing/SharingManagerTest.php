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

use Sonatra\Component\Security\Sharing\SharingIdentityConfig;
use Sonatra\Component\Security\Sharing\SharingManager;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockGroup;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockRole;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SharingManager
     */
    protected $sm;

    protected function setUp()
    {
        $this->sm = new SharingManager();
    }

    public function testHasConfig()
    {
        $pm = new SharingManager(array(
            new SharingIdentityConfig(MockRole::class),
        ));

        $this->assertTrue($pm->hasIdentityConfig(MockRole::class));
    }

    public function testHasNotConfig()
    {
        $this->assertFalse($this->sm->hasIdentityConfig(MockRole::class));
    }

    public function testAddConfig()
    {
        $this->assertFalse($this->sm->hasIdentityConfig(MockRole::class));

        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockRole::class));

        $this->assertTrue($this->sm->hasIdentityConfig(MockRole::class));
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\AlreadyConfigurationAliasExistingException
     * @expectedExceptionMessage The alias "foo" of sharing identity configuration for the class "Sonatra\Component\Security\Tests\Fixtures\Model\MockGroup
     */
    public function testAddConfigWithAlreadyExistingAlias()
    {
        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockRole::class, 'foo'));
        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockGroup::class, 'foo'));
    }

    public function testGetConfig()
    {
        $config = new SharingIdentityConfig(MockRole::class);
        $this->sm->addIdentityConfig($config);

        $this->assertTrue($this->sm->hasIdentityConfig(MockRole::class));
        $this->assertSame($config, $this->sm->getIdentityConfig(MockRole::class));
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\SharingIdentityConfigNotFoundException
     * @expectedExceptionMessage The sharing identity configuration for the class "Sonatra\Component\Security\Tests\Fixtures\Model\MockRole" is not found
     */
    public function testGetConfigWithNotManagedClass()
    {
        $this->sm->getIdentityConfig(MockRole::class);
    }

    public function testGetIdentityConfigs()
    {
        $config = new SharingIdentityConfig(MockRole::class);
        $this->sm->addIdentityConfig($config);

        $this->assertSame(array($config), $this->sm->getIdentityConfigs());
    }

    public function testHasIdentityRoleable()
    {
        $this->assertFalse($this->sm->hasIdentityRoleable());

        $config = new SharingIdentityConfig(MockRole::class, null, true);
        $this->sm->addIdentityConfig($config);

        $this->assertTrue($this->sm->hasIdentityRoleable());
    }

    public function testHasIdentityPermissible()
    {
        $this->assertFalse($this->sm->hasIdentityPermissible());

        $config = new SharingIdentityConfig(MockRole::class, null, false, true);
        $this->sm->addIdentityConfig($config);

        $this->assertTrue($this->sm->hasIdentityPermissible());
    }
}
