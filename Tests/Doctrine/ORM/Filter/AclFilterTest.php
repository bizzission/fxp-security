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
use Sonatra\Component\Security\Acl\Domain\AbstractRuleOrmFilterDefinition;
use Sonatra\Component\Security\Acl\Model\AclRuleManagerInterface;
use Sonatra\Component\Security\Acl\Model\RuleOrmFilterDefinitionInterface;
use Sonatra\Component\Security\Doctrine\ORM\Filter\AclFilter;
use Sonatra\Component\Security\Doctrine\ORM\Listener\AclListener;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclFilterTest extends \PHPUnit_Framework_TestCase
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
     * @var AclRuleManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $arm;

    /**
     * @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $targetClass;

    /**
     * @var AclListener|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aclListener;

    /**
     * @var AclFilter
     */
    protected $filter;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->arm = $this->getMockBuilder(AclRuleManagerInterface::class)->getMock();
        $this->eventManager = new EventManager();
        $this->filter = new AclFilter($this->em);
        $this->aclListener = $this->getMockBuilder(AclListener::class)->getMock();
        $this->eventManager->addEventListener(Events::postLoad, $this->getMockBuilder(EventSubscriber::class)->getMock());
        $this->eventManager->addEventListener(Events::postLoad, $this->aclListener);

        $this->em->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManager);

        $this->aclListener->expects($this->any())
            ->method('getAclRuleManager')
            ->willReturn($this->arm);

        $this->aclListener->expects($this->any())
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
     * @expectedExceptionMessage Listener "AclListener" was not added to the EventManager!
     */
    public function testAddFilterConstraintWithoutListener()
    {
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $evm = new EventManager();

        $em->expects($this->any())
            ->method('getEventManager')
            ->willReturn($evm);

        /* @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $filter = new AclFilter($em);
        $filter->addFilterConstraint($this->targetClass, 't');
    }

    public function testAddFilterConstraintWithRuleFilter()
    {
        $def = $this->getMockBuilder(RuleOrmFilterDefinitionInterface::class)->getMock();

        $this->arm->expects($this->once())
            ->method('getRule')
            ->with(BasicPermissionMap::PERMISSION_VIEW, \stdClass::class, null)
            ->willReturn('rule_test');

        $this->arm->expects($this->once())
            ->method('hasFilterDefinition')
            ->with('rule_test', AbstractRuleOrmFilterDefinition::TYPE)
            ->willReturn(true);

        $this->arm->expects($this->once())
            ->method('getFilterDefinition')
            ->with('rule_test', AbstractRuleOrmFilterDefinition::TYPE)
            ->willReturn($def);

        $def->expects($this->once())
            ->method('addFilterConstraint')
            ->willReturn('SQL_CONDITION');

        $this->assertSame('SQL_CONDITION', $this->filter->addFilterConstraint($this->targetClass, 't'));
    }

    public function testAddFilterConstraintWithoutRuleFilter()
    {
        $this->arm->expects($this->once())
            ->method('getRule')
            ->with(BasicPermissionMap::PERMISSION_VIEW, \stdClass::class, null)
            ->willReturn('rule_test');

        $this->arm->expects($this->once())
            ->method('hasFilterDefinition')
            ->with('rule_test', AbstractRuleOrmFilterDefinition::TYPE)
            ->willReturn(false);

        $this->assertSame('', $this->filter->addFilterConstraint($this->targetClass, 't'));
    }
}
