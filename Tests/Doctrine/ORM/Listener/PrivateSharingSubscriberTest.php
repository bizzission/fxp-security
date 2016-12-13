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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sonatra\Component\Security\Doctrine\ORM\Event\GetFilterEvent;
use Sonatra\Component\Security\Doctrine\ORM\Listener\PrivateSharingSubscriber;
use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\UserSecurityIdentity;
use Sonatra\Component\Security\Sharing\SharingIdentityConfigInterface;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Sonatra\Component\Security\SharingVisibilities;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObjectOwnerable;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObjectOwnerableOptional;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockRole;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockSharing;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PrivateSharingSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var Connection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $meta;

    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var PrivateSharingSubscriber
     */
    protected $listener;

    protected function setUp()
    {
        $this->connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $this->meta = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->with(MockSharing::class)
            ->willReturn($this->meta);

        $this->em->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connection);

        $this->listener = new PrivateSharingSubscriber($this->em, MockSharing::class, $this->tokenStorage);

        $this->assertCount(1, $this->listener->getSubscribedEvents());
    }

    public function testGetFilterWithEmptySecurityIdentities()
    {
        /* @var GetFilterEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetFilterEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getSecurityIdentities')
            ->willReturn(array());

        $event->expects($this->never())
            ->method('setFilter');

        $this->listener->getFilter($event);
    }

    public function testGetFilterWithNonPrivateVisibility()
    {
        /* @var GetFilterEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetFilterEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
            ->method('getSecurityIdentities')
            ->willReturn(array(
                new RoleSecurityIdentity('ROLE_USER'),
            ));

        $event->expects($this->once())
            ->method('getSharingVisibility')
            ->willReturn(SharingVisibilities::TYPE_PUBLIC);

        $event->expects($this->never())
            ->method('setFilter');

        $this->listener->getFilter($event);
    }

    public function testGetFilter()
    {
        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
            new UserSecurityIdentity('user.test'),
        );

        $dbPlatform = $this->getMockForAbstractClass(AbstractPlatform::class, array(), '', false, false, true, array('getDateTimeFormatString'));
        $dbPlatform->expects($this->once())
            ->method('getDateTimeFormatString')
            ->willReturn('Y-m-d h:m:s');

        $this->connection->expects($this->atLeastOnce())
            ->method('getDatabasePlatform')
            ->willReturn($dbPlatform);

        $this->connection->expects($this->atLeastOnce())
            ->method('quote')
            ->willReturnCallback(function ($value) {
                if (preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $value)) {
                    $value = 'DATETIME';
                }

                return '\''.$value.'\'';
            });

        /* @var GetFilterEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetFilterEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->atLeast(2))
            ->method('getSecurityIdentities')
            ->willReturn($sids);

        $targetEntity = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $targetEntity->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn(MockObject::class);

        $event->expects($this->once())
            ->method('getSharingVisibility')
            ->willReturn(SharingVisibilities::TYPE_PRIVATE);

        $event->expects($this->atLeastOnce())
            ->method('getTargetEntity')
            ->willReturn($targetEntity);

        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();

        $event->expects($this->once())
            ->method('getSharingManager')
            ->willReturn($sharingManager);

        $event->expects($this->once())
            ->method('getTargetTableAlias')
            ->willReturn('t0');

        $roleSidConfig = $this->getMockBuilder(SharingIdentityConfigInterface::class)->getMock();
        $roleSidConfig->expects($this->once())
            ->method('getType')
            ->willReturn(MockRole::class);

        $sharingManager->expects($this->at(0))
            ->method('getIdentityConfig')
            ->with('role')
            ->willReturn($roleSidConfig);

        $userSidConfig = $this->getMockBuilder(SharingIdentityConfigInterface::class)->getMock();
        $userSidConfig->expects($this->once())
            ->method('getType')
            ->willReturn(MockUserRoleable::class);

        $sharingManager->expects($this->at(1))
            ->method('getIdentityConfig')
            ->with('user')
            ->willReturn($userSidConfig);

        $this->meta->expects($this->once())
            ->method('getTableName')
            ->willReturn('test_sharing');

        $this->meta->expects($this->atLeastOnce())
            ->method('getColumnName')
            ->willReturnCallback(function ($value) {
                $map = array(
                    'subjectClass' => 'subject_class',
                    'subjectId' => 'subject_id',
                    'identityClass' => 'identity_class',
                    'identityName' => 'identity_name',
                    'enabled' => 'enabled',
                    'startedAt' => 'started_at',
                    'endedAt' => 'ended_at',
                    'id' => 'id',
                );

                return isset($map[$value]) ? $map[$value] : null;
            });

        $validFilter = <<<SELECTCLAUSE
t0.id IN (SELECT
    s.subject_id
FROM
    test_sharing s
WHERE
    s.subject_class = 'Sonatra\Component\Security\Tests\Fixtures\Model\MockObject'
    AND s.enabled IS TRUE
    AND (s.started_at IS NULL OR s.started_at <= 'DATETIME')
    AND (s.ended_at IS NULL OR s.ended_at >= 'DATETIME')
    AND ((s.identity_class = 'Sonatra\Component\Security\Tests\Fixtures\Model\MockRole' AND s.identity_name IN ('ROLE_USER')) OR (s.identity_class = 'Sonatra\Component\Security\Tests\Fixtures\Model\MockUserRoleable' AND s.identity_name IN ('user.test')))
GROUP BY
    s.subject_id)
SELECTCLAUSE;

        $event->expects($this->once())
            ->method('setFilter')
            ->with($validFilter);

        $this->listener->getFilter($event);
    }

    public function getCurrentUserValues()
    {
        return array(
            array(MockObjectOwnerable::class, false),
            array(MockObjectOwnerable::class, true),
            array(MockObjectOwnerableOptional::class, false),
            array(MockObjectOwnerableOptional::class, true),
        );
    }

    /**
     * @dataProvider getCurrentUserValues
     *
     * @param string $objectClass
     * @param bool   $withCurrentUser
     */
    public function testGetFilterWithOwnerableObject($objectClass, $withCurrentUser)
    {
        $token = null;

        if ($withCurrentUser) {
            $token = $this->getMockBuilder(TokenInterface::class)->getMock();
            $token->expects($this->once())
                ->method('getUser')
                ->willReturn(new MockUserRoleable());
        }

        $this->tokenStorage->expects($this->atLeastOnce())
            ->method('getToken')
            ->willReturn($token);

        $sids = array(
            new RoleSecurityIdentity('ROLE_USER'),
            new UserSecurityIdentity('user.test'),
        );

        $dbPlatform = $this->getMockForAbstractClass(AbstractPlatform::class, array(), '', false, false, true, array('getDateTimeFormatString'));
        $dbPlatform->expects($this->once())
            ->method('getDateTimeFormatString')
            ->willReturn('Y-m-d h:m:s');

        $this->connection->expects($this->atLeastOnce())
            ->method('getDatabasePlatform')
            ->willReturn($dbPlatform);

        $this->connection->expects($this->atLeastOnce())
            ->method('quote')
            ->willReturnCallback(function ($value) {
                if (preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $value)) {
                    $value = 'DATETIME';
                }

                return '\''.$value.'\'';
            });

        /* @var GetFilterEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetFilterEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->atLeast(2))
            ->method('getSecurityIdentities')
            ->willReturn($sids);

        $targetEntity = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $targetEntity->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn($objectClass);

        $targetEntity->expects($this->atLeastOnce())
            ->method('getAssociationMapping')
            ->willReturnCallback(function ($value) {
                $map = array(
                    'owner' => array(
                        'joinColumnFieldNames' => array(
                            'owner' => 'owner_id',
                        ),
                    ),
                );

                return isset($map[$value]) ? $map[$value] : null;
            });

        $event->expects($this->once())
            ->method('getSharingVisibility')
            ->willReturn(SharingVisibilities::TYPE_PRIVATE);

        $event->expects($this->atLeastOnce())
            ->method('getTargetEntity')
            ->willReturn($targetEntity);

        $sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();

        $event->expects($this->once())
            ->method('getSharingManager')
            ->willReturn($sharingManager);

        $event->expects($this->atLeast(2))
            ->method('getTargetTableAlias')
            ->willReturn('t0');

        $roleSidConfig = $this->getMockBuilder(SharingIdentityConfigInterface::class)->getMock();
        $roleSidConfig->expects($this->once())
            ->method('getType')
            ->willReturn(MockRole::class);

        $sharingManager->expects($this->at(0))
            ->method('getIdentityConfig')
            ->with('role')
            ->willReturn($roleSidConfig);

        $userSidConfig = $this->getMockBuilder(SharingIdentityConfigInterface::class)->getMock();
        $userSidConfig->expects($this->once())
            ->method('getType')
            ->willReturn(MockUserRoleable::class);

        $sharingManager->expects($this->at(1))
            ->method('getIdentityConfig')
            ->with('user')
            ->willReturn($userSidConfig);

        $this->meta->expects($this->once())
            ->method('getTableName')
            ->willReturn('test_sharing');

        $this->meta->expects($this->atLeastOnce())
            ->method('getColumnName')
            ->willReturnCallback(function ($value) {
                $map = array(
                    'subjectClass' => 'subject_class',
                    'subjectId' => 'subject_id',
                    'identityClass' => 'identity_class',
                    'identityName' => 'identity_name',
                    'enabled' => 'enabled',
                    'startedAt' => 'started_at',
                    'endedAt' => 'ended_at',
                    'id' => 'id',
                );

                return isset($map[$value]) ? $map[$value] : null;
            });

        $ownerFilter = $withCurrentUser
            ? 't0.owner_id = \'50\''
            : 't0.owner_id IS NULL';

        if ($withCurrentUser && $objectClass === MockObjectOwnerableOptional::class) {
            $ownerFilter .= ' OR t0.owner_id IS NULL';
        }

        $validFilter = <<<SELECTCLAUSE
{$ownerFilter}
    OR
(t0.id IN (SELECT
    s.subject_id
FROM
    test_sharing s
WHERE
    s.subject_class = '{$objectClass}'
    AND s.enabled IS TRUE
    AND (s.started_at IS NULL OR s.started_at <= 'DATETIME')
    AND (s.ended_at IS NULL OR s.ended_at >= 'DATETIME')
    AND ((s.identity_class = 'Sonatra\Component\Security\Tests\Fixtures\Model\MockRole' AND s.identity_name IN ('ROLE_USER')) OR (s.identity_class = 'Sonatra\Component\Security\Tests\Fixtures\Model\MockUserRoleable' AND s.identity_name IN ('user.test')))
GROUP BY
    s.subject_id))
SELECTCLAUSE;

        $event->expects($this->once())
            ->method('setFilter')
            ->with($validFilter);

        $this->listener->getFilter($event);
    }
}
