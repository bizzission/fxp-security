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
use Sonatra\Component\Security\Acl\Model\AclManagerInterface;
use Sonatra\Component\Security\Acl\Model\AclObjectFilterInterface;
use Sonatra\Component\Security\Acl\Model\AclRuleManagerInterface;
use Sonatra\Component\Security\Core\Token\ConsoleToken;
use Sonatra\Component\Security\Doctrine\ORM\Listener\AclListener;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclListenerTest extends \PHPUnit_Framework_TestCase
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
     * @var AclManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aclManager;

    /**
     * @var AclRuleManagerInterface
     */
    protected $aclRuleManager;

    /**
     * @var AclObjectFilterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aclObjectFilter;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var UnitOfWork|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uow;

    /**
     * @var AclListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->authChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->getMock();
        $this->aclManager = $this->getMockBuilder(AclManagerInterface::class)->getMock();
        $this->aclRuleManager = $this->getMockBuilder(AclRuleManagerInterface::class)->getMock();
        $this->aclObjectFilter = $this->getMockBuilder(AclObjectFilterInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->uow = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();
        $this->listener = new AclListener();

        $this->em->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $this->listener->setTokenStorage($this->tokenStorage);
        $this->listener->setAuthorizationChecker($this->authChecker);
        $this->listener->setAclManager($this->aclManager);
        $this->listener->setAclRuleManager($this->aclRuleManager);
        $this->listener->setAclObjectFilter($this->aclObjectFilter);

        $this->assertCount(3, $this->listener->getSubscribedEvents());
    }

    public function getInvalidInitMethods()
    {
        return array(
            array('setTokenStorage', array()),
            array('setAuthorizationChecker', array('tokenStorage')),
            array('setAclManager', array('tokenStorage', 'authChecker')),
            array('setAclRuleManager', array('tokenStorage', 'authChecker', 'aclManager')),
            array('setAclObjectFilter', array('tokenStorage', 'authChecker', 'aclManager', 'aclRuleManager')),
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
        $msg = sprintf('The "%s()" method must ba called before the init of the doctrine orm acl listener', $method);
        $this->expectExceptionMessage($msg);

        $listener = new AclListener();

        if (in_array('tokenStorage', $setters)) {
            $listener->setTokenStorage($this->tokenStorage);
        }

        if (in_array('authChecker', $setters)) {
            $listener->setAuthorizationChecker($this->authChecker);
        }

        if (in_array('aclManager', $setters)) {
            $listener->setAclManager($this->aclManager);
        }

        if (in_array('aclRuleManager', $setters)) {
            $listener->setAclRuleManager($this->aclRuleManager);
        }

        if (in_array('aclObjectFilter', $setters)) {
            $listener->setAclObjectFilter($this->aclObjectFilter);
        }

        $listener->getAclManager();
    }

    public function testManagerGetters()
    {
        $this->assertInstanceOf(TokenStorageInterface::class, $this->listener->getTokenStorage());
        $this->assertInstanceOf(AuthorizationCheckerInterface::class, $this->listener->getAuthorizationChecker());
        $this->assertInstanceOf(AclManagerInterface::class, $this->listener->getAclManager());
        $this->assertInstanceOf(AclRuleManagerInterface::class, $this->listener->getAclRuleManager());
        $this->assertInstanceOf(AclObjectFilterInterface::class, $this->listener->getAclObjectFilter());
    }

    public function testGetSecurityIdentities()
    {
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $sids = array(
            new RoleSecurityIdentity('ROLE_TEST'),
        );

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->aclManager->expects($this->once())
            ->method('getSecurityIdentities')
            ->with($token)
            ->willReturn($sids);

        $this->assertSame($sids, $this->listener->getSecurityIdentities());
    }

    public function testPostFlush()
    {
        $this->aclManager->expects($this->once())
            ->method('resetPreloadAcls')
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

        $this->aclManager->expects($this->once())
            ->method('isDisabled')
            ->willReturn(true);

        $this->aclObjectFilter->expects($this->never())
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

        $this->aclObjectFilter->expects($this->never())
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

        $this->aclObjectFilter->expects($this->never())
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

        $this->aclManager->expects($this->once())
            ->method('isDisabled')
            ->willReturn(false);

        $args->expects($this->once())
            ->method('getEntity')
            ->willReturn($entity);

        $this->aclObjectFilter->expects($this->once())
            ->method('filter')
            ->with($entity);

        $this->listener->postLoad($args);
    }

    //////////////////////////////////////////

    public function testOnFlushWithDisabledAclManager()
    {
        /* @var OnFlushEventArgs $args */
        $args = $this->getMockBuilder(OnFlushEventArgs::class)->disableOriginalConstructor()->getMock();
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->aclManager->expects($this->once())
            ->method('isDisabled')
            ->willReturn(true);

        $this->aclObjectFilter->expects($this->never())
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

        $this->aclObjectFilter->expects($this->never())
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

        $this->aclObjectFilter->expects($this->never())
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

        $this->aclManager->expects($this->once())
            ->method('isDisabled')
            ->willReturn(false);

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->aclObjectFilter->expects($this->once())
            ->method('beginTransaction');

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn(array($object));

        $this->aclObjectFilter->expects($this->once())
            ->method('restore');

        $this->authChecker->expects($this->once())
            ->method('isGranted')
            ->with(BasicPermissionMap::PERMISSION_CREATE, $object)
            ->willReturn(false);

        $this->listener->onFlush($args);
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\AccessDeniedException
     * @expectedExceptionMessage Insufficient privilege to update the entity
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

        $this->aclManager->expects($this->once())
            ->method('isDisabled')
            ->willReturn(false);

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->aclObjectFilter->expects($this->once())
            ->method('beginTransaction');

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn(array());

        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn(array($object));

        $this->aclObjectFilter->expects($this->once())
            ->method('restore');

        $this->authChecker->expects($this->once())
            ->method('isGranted')
            ->with(BasicPermissionMap::PERMISSION_EDIT, $object)
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

        $this->aclManager->expects($this->once())
            ->method('isDisabled')
            ->willReturn(false);

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->aclObjectFilter->expects($this->once())
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
            ->with(BasicPermissionMap::PERMISSION_DELETE, $object)
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

        $this->aclManager->expects($this->once())
            ->method('isDisabled')
            ->willReturn(false);

        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($this->em);

        $this->aclObjectFilter->expects($this->once())
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

        $this->aclObjectFilter->expects($this->once())
            ->method('commit');

        $this->listener->onFlush($args);
    }
}
