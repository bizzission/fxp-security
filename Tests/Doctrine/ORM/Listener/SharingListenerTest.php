<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Doctrine\ORM\Listener;

use Sonatra\Component\Security\Doctrine\ORM\Listener\SharingListener;
use Sonatra\Component\Security\Identity\SecurityIdentityManagerInterface;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PermissionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $permissionManager;

    /**
     * @var SharingManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharingManager;

    /**
     * @var SecurityIdentityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidManager;

    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var SharingListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->permissionManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $this->sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->eventDispatcher = new EventDispatcher();
        $this->listener = new SharingListener();

        $this->listener->setPermissionManager($this->permissionManager);
        $this->listener->setSharingManager($this->sharingManager);
        $this->listener->setSecurityIdentityManager($this->sidManager);
        $this->listener->setTokenStorage($this->tokenStorage);
        $this->listener->setEventDispatcher($this->eventDispatcher);

        $this->assertCount(1, $this->listener->getSubscribedEvents());
    }

    public function getInvalidInitMethods()
    {
        return array(
            array('setPermissionManager', array()),
            array('setSharingManager', array('permissionManager')),
            array('setSecurityIdentityManager', array('permissionManager', 'sharingManager')),
            array('setTokenStorage', array('permissionManager', 'sharingManager', 'sidManager')),
            array('setEventDispatcher', array('permissionManager', 'sharingManager', 'sidManager', 'tokenStorage')),
        );
    }

    /**
     * @dataProvider getInvalidInitMethods
     *
     * @expectedException \Sonatra\Component\Security\Exception\SecurityException
     *
     * @param string   $method  The method
     * @param string[] $setters The setters
     */
    public function testInvalidInit($method, array $setters)
    {
        $msg = sprintf('The "%s()" method must be called before the init of the "Sonatra\Component\Security\Doctrine\ORM\Listener\SharingListener" class', $method);
        $this->expectExceptionMessage($msg);

        $listener = new SharingListener();

        if (in_array('permissionManager', $setters)) {
            $listener->setPermissionManager($this->permissionManager);
        }

        if (in_array('sharingManager', $setters)) {
            $listener->setSharingManager($this->sharingManager);
        }

        if (in_array('sidManager', $setters)) {
            $listener->setSecurityIdentityManager($this->sidManager);
        }

        if (in_array('tokenStorage', $setters)) {
            $listener->setTokenStorage($this->tokenStorage);
        }

        if (in_array('eventDispatcher', $setters)) {
            $listener->setEventDispatcher($this->eventDispatcher);
        }

        $listener->getPermissionManager();
    }

    public function testGetPermissionManager()
    {
        $this->assertSame($this->permissionManager, $this->listener->getPermissionManager());
    }

    public function testGetSharingManager()
    {
        $this->assertSame($this->sharingManager, $this->listener->getSharingManager());
    }

    public function testGetSecurityIdentityManager()
    {
        $this->assertSame($this->sidManager, $this->listener->getSecurityIdentityManager());
    }

    public function testGetTokenStorage()
    {
        $this->assertSame($this->tokenStorage, $this->listener->getTokenStorage());
    }

    public function testGetEventDispatcher()
    {
        $this->assertSame($this->eventDispatcher, $this->listener->getEventDispatcher());
    }

    public function testOnFLush()
    {
        $this->listener->onFlush();
    }
}
