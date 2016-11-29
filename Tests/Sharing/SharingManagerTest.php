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

use Sonatra\Component\Security\Identity\SubjectIdentityInterface;
use Sonatra\Component\Security\Sharing\SharingIdentityConfig;
use Sonatra\Component\Security\Sharing\SharingManager;
use Sonatra\Component\Security\Sharing\SharingSubjectConfig;
use Sonatra\Component\Security\SharingVisibilities;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockGroup;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;
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

    public function testHasSubjectConfig()
    {
        $pm = new SharingManager(array(
            new SharingSubjectConfig(MockObject::class),
        ));

        $this->assertTrue($pm->hasSubjectConfig(MockObject::class));
    }

    public function testHasIdentityConfig()
    {
        $pm = new SharingManager(array(), array(
            new SharingIdentityConfig(MockRole::class),
        ));

        $this->assertTrue($pm->hasIdentityConfig(MockRole::class));
    }

    public function testHasNotSubjectConfig()
    {
        $this->assertFalse($this->sm->hasSubjectConfig(MockObject::class));
    }

    public function testHasNotIdentityConfig()
    {
        $this->assertFalse($this->sm->hasIdentityConfig(MockRole::class));
    }

    public function testAddSubjectConfig()
    {
        $this->assertFalse($this->sm->hasSubjectConfig(MockObject::class));

        $this->sm->addSubjectConfig(new SharingSubjectConfig(MockObject::class));

        $this->assertTrue($this->sm->hasSubjectConfig(MockObject::class));
    }

    public function testAddIdentityConfig()
    {
        $this->assertFalse($this->sm->hasIdentityConfig(MockRole::class));

        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockRole::class));

        $this->assertTrue($this->sm->hasIdentityConfig(MockRole::class));
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\AlreadyConfigurationAliasExistingException
     * @expectedExceptionMessage The alias "foo" of sharing identity configuration for the class "Sonatra\Component\Security\Tests\Fixtures\Model\MockGroup
     */
    public function testAddIdentityConfigWithAlreadyExistingAlias()
    {
        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockRole::class, 'foo'));
        $this->sm->addIdentityConfig(new SharingIdentityConfig(MockGroup::class, 'foo'));
    }

    public function testGetSubjectConfig()
    {
        $config = new SharingSubjectConfig(MockObject::class);
        $this->sm->addSubjectConfig($config);

        $this->assertTrue($this->sm->hasSubjectConfig(MockObject::class));
        $this->assertSame($config, $this->sm->getSubjectConfig(MockObject::class));
    }

    public function testGetIdentityConfig()
    {
        $config = new SharingIdentityConfig(MockRole::class);
        $this->sm->addIdentityConfig($config);

        $this->assertTrue($this->sm->hasIdentityConfig(MockRole::class));
        $this->assertSame($config, $this->sm->getIdentityConfig(MockRole::class));
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\SharingSubjectConfigNotFoundException
     * @expectedExceptionMessage The sharing subject configuration for the class "Sonatra\Component\Security\Tests\Fixtures\Model\MockRole" is not found
     */
    public function testGetSubjectConfigWithNotManagedClass()
    {
        $this->sm->getSubjectConfig(MockRole::class);
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\SharingIdentityConfigNotFoundException
     * @expectedExceptionMessage The sharing identity configuration for the class "Sonatra\Component\Security\Tests\Fixtures\Model\MockRole" is not found
     */
    public function testGetIdentityConfigWithNotManagedClass()
    {
        $this->sm->getIdentityConfig(MockRole::class);
    }

    public function testGetSubjectConfigs()
    {
        $config = new SharingSubjectConfig(MockRole::class);
        $this->sm->addSubjectConfig($config);

        $this->assertSame(array($config), $this->sm->getSubjectConfigs());
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

    public function testHasSharingVisibilityWithoutConfig()
    {
        /* @var SubjectIdentityInterface|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();
        $subject->expects($this->once())
            ->method('getType')
            ->willReturn(MockObject::class);

        $this->assertFalse($this->sm->hasSharingVisibility($subject));
    }

    public function getSharingVisibilities()
    {
        return array(
            array(SharingVisibilities::TYPE_NONE, false),
            array(SharingVisibilities::TYPE_PUBLIC, true),
            array(SharingVisibilities::TYPE_PRIVATE, true),
        );
    }

    /**
     * @dataProvider getSharingVisibilities
     *
     * @param string $visibility The sharing visibility
     * @param bool   $result     The result
     */
    public function testHasSharingVisibility($visibility, $result)
    {
        /* @var SubjectIdentityInterface|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();
        $subject->expects($this->once())
            ->method('getType')
            ->willReturn(MockObject::class);

        $this->sm->addSubjectConfig(new SharingSubjectConfig(MockObject::class, $visibility));
        $this->sm->addSubjectConfig(new SharingSubjectConfig(MockObject::class, $visibility));

        $this->assertSame($result, $this->sm->hasSharingVisibility($subject));
    }
}
