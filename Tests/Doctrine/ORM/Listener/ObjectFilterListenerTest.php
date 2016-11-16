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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Sonatra\Component\Security\Token\ConsoleToken;
use Sonatra\Component\Security\Doctrine\ORM\Listener\ObjectFilterListener;
use Sonatra\Component\Security\ObjectFilter\ObjectFilterInterface;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectFilterListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authChecker;

    /**
     * @var PermissionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $permissionManager;

    /**
     * @var ObjectFilterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectFilter;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var UnitOfWork|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uow;

    /**
     * @var ObjectFilterListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $this->permissionManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $this->objectFilter = $this->getMockBuilder(ObjectFilterInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->uow = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
        $this->listener = new ObjectFilterListener();

        $this->em->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $this->listener->setTokenStorage($this->tokenStorage);
        $this->listener->setAuthorizationChecker($this->authChecker);
        $this->listener->setPermissionManager($this->permissionManager);
        $this->listener->setObjectFilter($this->objectFilter);

        $this->assertCount(3, $this->listener->getSubscribedEvents());
    }

    public function getInvalidInitMethods()
    {
        return array(
            array('setTokenStorage', array()),
            array('setAuthorizationChecker', array('tokenStorage')),
            array('setPermissionManager', array('tokenStorage', 'authChecker')),
            array('setObjectFilter', array('tokenStorage', 'authChecker', 'permissionManager')),
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
        $msg = sprintf('The "%s()" method must be called before the init of the doctrine orm object filter listener', $method);
        $this->expectExceptionMessage($msg);

        $listener = new ObjectFilterListener();

        if (in_array('tokenStorage', $setters)) {
            $listener->setTokenStorage($this->tokenStorage);
        }

        if (in_array('authChecker', $setters)) {
            $listener->setAuthorizationChecker($this->authChecker);
        }

        if (in_array('permissionManager', $setters)) {
            $listener->setPermissionManager($this->permissionManager);
        }

        if (in_array('objectFilter', $setters)) {
            $listener->setObjectFilter($this->objectFilter);
        }

        /* @var LifecycleEventArgs $args */
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();

        $listener->postLoad($args);
    }

    public function testPostFlush()
    {
        $this->permissionManager->expects($this->once())
            ->method('resetPreloadPermissions')
            ->with(array());

        $this->listener->postFlush();
    }

    public function testPostLoadWithDisabledAclManager()
    {
        /* @var LifecycleEventArgs $args */
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->permissionManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->objectFilter->expects($this->never())
            ->method('filter');

        $this->listener->postLoad($args);
    }

    public function testPostLoadWithEmptyToken()
    {
        /* @var LifecycleEventArgs $args */
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->objectFilter->expects($this->never())
            ->method('filter');

        $this->listener->postLoad($args);
    }

    public function testPostLoadWithConsoleToken()
    {
        /* @var LifecycleEventArgs $args */
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(ConsoleToken::class)->disableOriginalConstructor()->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->objectFilter->expects($this->never())
            ->method('filter');

        $this->listener->postLoad($args);
    }

    public function testPostLoad()
    {
        /* @var LifecycleEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(LifecycleEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $entity = new \stdClass();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->permissionManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $args->expects($this->once())
            ->method('getEntity')
            ->willReturn($entity);

        $this->objectFilter->expects($this->once())
            ->method('filter')
            ->with($entity);

        $this->listener->postLoad($args);
    }

    public function testOnFlushWithDisabledAclManager()
    {
        /* @var OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->permissionManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->objectFilter->expects($this->never())
            ->method('filter');

        $this->listener->onFlush($args);
    }

    public function testOnFlushWithEmptyToken()
    {
        /* @var OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn(null);

        $this->objectFilter->expects($this->never())
            ->method('filter');

        $this->listener->onFlush($args);
    }

    public function testOnFlushWithConsoleToken()
    {
        /* @var OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(ConsoleToken::class)->disableOriginalConstructor()->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->objectFilter->expects($this->never())
            ->method('filter');

        $this->listener->onFlush($args);
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\AccessDeniedException
     * @expectedExceptionMessage Insufficient privilege to create the entity
     */
    public function testOnFLushWithInsufficientPrivilegeToCreateEntity()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $object = $this->getMockBuilder(\stdClass::class)->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->permissionManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->objectFilter->expects($this->once())
            ->method('beginTransaction');

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn(array($object));

        $this->objectFilter->expects($this->once())
            ->method('restore');

        $this->authChecker->expects($this->once())
            ->method('isGranted')
            ->with('create', $object)
            ->willReturn(false);

        $this->listener->onFlush($args);
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\AccessDeniedException
     * @expectedExceptionMessage Insufficient privilege to edit the entity
     */
    public function testOnFLushWithInsufficientPrivilegeToUpdateEntity()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $object = $this->getMockBuilder(\stdClass::class)->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->permissionManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->objectFilter->expects($this->once())
            ->method('beginTransaction');

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn(array());

        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn(array($object));

        $this->objectFilter->expects($this->once())
            ->method('restore');

        $this->authChecker->expects($this->once())
            ->method('isGranted')
            ->with('edit', $object)
            ->willReturn(false);

        $this->listener->onFlush($args);
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\AccessDeniedException
     * @expectedExceptionMessage Insufficient privilege to delete the entity
     */
    public function testOnFLushWithInsufficientPrivilegeToDeleteEntity()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $object = $this->getMockBuilder(\stdClass::class)->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->permissionManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->objectFilter->expects($this->once())
            ->method('beginTransaction');

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn(array());

        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn(array());

        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn(array($object));

        $this->authChecker->expects($this->once())
            ->method('isGranted')
            ->with('delete', $object)
            ->willReturn(false);

        $this->listener->onFlush($args);
    }

    public function testOnFLush()
    {
        /* @var OnFlushEventArgs|\PHPUnit_Framework_MockObject_MockObject $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->permissionManager->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->objectFilter->expects($this->once())
            ->method('beginTransaction');

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn(array());

        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn(array());

        $this->uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn(array());

        $this->objectFilter->expects($this->once())
            ->method('commit');

        $this->listener->onFlush($args);
    }
}
