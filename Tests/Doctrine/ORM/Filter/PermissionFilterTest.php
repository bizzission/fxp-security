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
use Sonatra\Component\Security\Doctrine\ORM\Filter\PermissionFilter;
use Sonatra\Component\Security\Doctrine\ORM\Listener\PermissionListener;
use Sonatra\Component\Security\Identity\RoleSecurityIdentity;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionFilterTest extends \PHPUnit_Framework_TestCase
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
     * @var PermissionListener|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $permissionListener;

    /**
     * @var PermissionFilter
     */
    protected $filter;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->eventManager = new EventManager();
        $this->filter = new PermissionFilter($this->em);
        $this->permissionListener = $this->getMockBuilder(PermissionListener::class)->getMock();
        $this->eventManager->addEventListener(Events::postLoad, $this->getMockBuilder(EventSubscriber::class)->getMock());
        $this->eventManager->addEventListener(Events::postLoad, $this->permissionListener);

        $this->em->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManager);

        $this->permissionListener->expects($this->any())
            ->method('getSecurityIdentities')
            ->willReturn(array(
                new RoleSecurityIdentity('ROLE_TEST'),
            ));

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
            ->willReturn(\stdClass::class);
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\RuntimeException
     * @expectedExceptionMessage Listener "PermissionListener" was not added to the EventManager!
     */
    public function testAddFilterConstraintWithoutListener()
    {
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $evm = new EventManager();

        $em->expects($this->any())
            ->method('getEventManager')
            ->willReturn($evm);

        /* @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $filter = new PermissionFilter($em);
        $filter->addFilterConstraint($this->targetClass, 't');
    }

    public function testAddFilterConstraint()
    {
        $this->assertSame('', $this->filter->addFilterConstraint($this->targetClass, 't'));
    }
}
