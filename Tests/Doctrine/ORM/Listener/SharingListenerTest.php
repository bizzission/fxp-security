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

use Doctrine\ORM\Event\OnFlushEventArgs;
use Sonatra\Component\Security\Doctrine\ORM\Listener\SharingListener;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;

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
     * @var SharingListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->permissionManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $this->listener = new SharingListener();

        $this->listener->setPermissionManager($this->permissionManager);

        $this->assertCount(1, $this->listener->getSubscribedEvents());
    }

    public function getInvalidInitMethods()
    {
        return array(
            array('setPermissionManager', array()),
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
        $msg = sprintf('The "%s()" method must be called before the init of the doctrine orm sharing listener', $method);
        $this->expectExceptionMessage($msg);

        $listener = new SharingListener();

        if (in_array('permissionManager', $setters)) {
            $listener->setPermissionManager($this->permissionManager);
        }

        $listener->getPermissionManager();
    }

    public function testGetPermissionManager()
    {
        $pm = $this->listener->getPermissionManager();

        $this->assertSame($this->permissionManager, $pm);
    }

    public function testOnFLush()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();

        $this->listener->onFlush($args);
    }
}
