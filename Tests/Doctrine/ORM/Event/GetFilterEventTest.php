<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Doctrine\ORM\Event;

use Doctrine\ORM\Mapping\ClassMetadata;
use Sonatra\Component\Security\Doctrine\ORM\Event\GetFilterEvent;
use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\SecurityIdentityManagerInterface;
use Sonatra\Component\Security\Identity\SubjectIdentityInterface;
use Sonatra\Component\Security\Identity\UserSecurityIdentity;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Sonatra\Component\Security\SharingVisibilities;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GetFilterEventTest extends \PHPUnit_Framework_TestCase
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
     * @var SubjectIdentityInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var SecurityIdentityInterface[]
     */
    protected $sids;

    /**
     * @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $targetEntity;

    /**
     * @var GetFilterEvent
     */
    protected $event;

    protected function setUp()
    {
        $this->permissionManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $this->sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->subject = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();
        $this->targetEntity = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $this->sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
            new UserSecurityIdentity('user.test'),
        );

        $this->event = new GetFilterEvent(
            $this->permissionManager,
            $this->sharingManager,
            $this->subject,
            SharingVisibilities::TYPE_PRIVATE,
            $this->sids,
            $this->targetEntity,
            't0'
        );
    }

    public function testGetters()
    {
        $this->assertSame($this->permissionManager, $this->event->getPermissionManager());
        $this->assertSame($this->sharingManager, $this->event->getSharingManager());
        $this->assertSame($this->subject, $this->event->getSubject());
        $this->assertSame($this->sids, $this->event->getSecurityIdentities());
        $this->assertSame($this->targetEntity, $this->event->getTargetEntity());
        $this->assertSame('t0', $this->event->getTargetTableAlias());
        $this->assertSame(SharingVisibilities::TYPE_PRIVATE, $this->event->getSharingVisibility());
        $this->assertSame('', $this->event->getFilter());
    }

    public function testSetFilter()
    {
        $this->assertSame('', $this->event->getFilter());

        $this->event->setFilter('TEST_FILTER');

        $this->assertSame('TEST_FILTER', $this->event->getFilter());
    }
}
