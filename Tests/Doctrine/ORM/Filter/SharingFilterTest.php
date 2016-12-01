<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Doctrine\ORM\Filter;

use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sonatra\Component\Security\Doctrine\ORM\Event\GetFilterEvent;
use Sonatra\Component\Security\Doctrine\ORM\Filter\SharingFilter;
use Sonatra\Component\Security\Doctrine\ORM\Listener\SharingListener;
use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityManagerInterface;
use Sonatra\Component\Security\Identity\SubjectIdentity;
use Sonatra\Component\Security\Identity\UserSecurityIdentity;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Sonatra\Component\Security\SharingFilterEvents;
use Sonatra\Component\Security\SharingVisibilities;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $targetClass;

    /**
     * @var SharingListener|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sharingListener;

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
     * @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $token;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var SharingFilter
     */
    protected $filter;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->eventManager = new EventManager();
        $this->filter = new SharingFilter($this->em);
        $this->sharingListener = $this->getMockBuilder(SharingListener::class)->getMock();
        $this->permissionManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $this->sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->token = $this->getMockBuilder(TokenInterface::class)->getMock();
        $this->eventDispatcher = new EventDispatcher();

        $this->eventManager->addEventListener(Events::postLoad, $this->getMockBuilder(EventSubscriber::class)->getMock());
        $this->eventManager->addEventListener(Events::postLoad, $this->sharingListener);

        $this->em->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManager);

        $this->targetClass = $this->getMockForAbstractClass(
            ClassMetadata::class,
            array(),
            '',
            false,
            true,
            true,
            array(
                'getName',
            )
        );

        $this->targetClass->expects($this->any())
            ->method('getName')
            ->willReturn(MockObject::class);

        $this->tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn($this->token);
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\RuntimeException
     * @expectedExceptionMessage The listener "Sonatra\Component\Security\Doctrine\ORM\SharingListener" was not added to the Doctrine ORM Event Manager
     */
    public function testAddFilterConstraintWithoutListener()
    {
        /* @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $evm = new EventManager();

        $em->expects($this->any())
            ->method('getEventManager')
            ->willReturn($evm);

        $filter = new SharingFilter($em);
        $filter->addFilterConstraint($this->targetClass, 't');
    }

    public function testAddFilterConstraint()
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
            new UserSecurityIdentity('user.test'),
        );

        $this->sharingListener->expects($this->once())
            ->method('getPermissionManager')
            ->willReturn($this->permissionManager);

        $this->sharingListener->expects($this->once())
            ->method('getSharingManager')
            ->willReturn($this->sharingManager);

        $this->sharingListener->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->eventDispatcher);

        $this->sharingListener->expects($this->once())
            ->method('getSecurityIdentityManager')
            ->willReturn($this->sidManager);

        $this->sharingListener->expects($this->once())
            ->method('getTokenStorage')
            ->willReturn($this->tokenStorage);

        $this->sharingManager->expects($this->once())
            ->method('hasSharingVisibility')
            ->with(SubjectIdentity::fromClassname(MockObject::class))
            ->willReturn(true);

        $this->sharingManager->expects($this->once())
            ->method('getSharingVisibility')
            ->with(SubjectIdentity::fromClassname(MockObject::class))
            ->willReturn(SharingVisibilities::TYPE_PRIVATE);

        $this->sidManager->expects($this->once())
            ->method('getSecurityIdentities')
            ->with($this->token)
            ->willReturn($sids);

        $eventAction = false;

        $this->eventDispatcher->addListener(SharingFilterEvents::DOCTRINE_ORM_FILTER, function (GetFilterEvent $event) use (&$eventAction) {
            $eventAction = true;
            $event->setFilter('FILTER_TEST');
        });

        $this->assertSame('FILTER_TEST', $this->filter->addFilterConstraint($this->targetClass, 't'));
    }
}
