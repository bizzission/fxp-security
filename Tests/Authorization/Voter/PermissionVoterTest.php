<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Authorization\Voter;

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Security\Authorization\Voter\PermissionVoter;
use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityManagerInterface;
use Sonatra\Component\Security\Permission\FieldVote;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionVoterTest extends TestCase
{
    /**
     * @var PermissionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $permManager;

    /**
     * @var SecurityIdentityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidManager;

    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var PermissionVoter
     */
    protected $voter;

    protected function setUp()
    {
        $this->permManager = $this->getMockBuilder(PermissionManagerInterface::class)->getMock();
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $this->permManager->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);

        $this->voter = new PermissionVoter(
            $this->permManager,
            $this->sidManager
        );
    }

    public function getVoteAttributes()
    {
        $class = MockObject::class;
        $object = new MockObject('foo');
        $fieldVote = new FieldVote($object, 'name');
        $arrayValid = array($object, 'name');
        $arrayInvalid = array($object);

        return array(
            array(array(42), $class, VoterInterface::ACCESS_ABSTAIN),
            array(array(42), $object, VoterInterface::ACCESS_ABSTAIN),
            array(array(42), $fieldVote, VoterInterface::ACCESS_ABSTAIN),
            array(array(42), $arrayValid, VoterInterface::ACCESS_ABSTAIN),
            array(array(42), $arrayInvalid, VoterInterface::ACCESS_ABSTAIN),
            array(array('view'), $class, VoterInterface::ACCESS_ABSTAIN),
            array(array('view'), $object, VoterInterface::ACCESS_ABSTAIN),
            array(array('view'), $fieldVote, VoterInterface::ACCESS_ABSTAIN),
            array(array('view'), $arrayValid, VoterInterface::ACCESS_ABSTAIN),
            array(array('view'), $arrayInvalid, VoterInterface::ACCESS_ABSTAIN),
            array(array('perm_view'), $class, VoterInterface::ACCESS_GRANTED, true),
            array(array('perm_view'), $object, VoterInterface::ACCESS_GRANTED, true),
            array(array('perm_view'), $object, VoterInterface::ACCESS_DENIED, false),
            array(array('perm_view'), $fieldVote, VoterInterface::ACCESS_GRANTED, true),
            array(array('perm_view'), $fieldVote, VoterInterface::ACCESS_DENIED, false),
            array(array('perm_view'), $arrayValid, VoterInterface::ACCESS_GRANTED, true),
            array(array('perm_view'), $arrayValid, VoterInterface::ACCESS_DENIED, false),
            array(array('perm_view'), $arrayInvalid, VoterInterface::ACCESS_ABSTAIN),
            array(array('foo'), null, VoterInterface::ACCESS_ABSTAIN),
            array(array('perm_foo'), null, VoterInterface::ACCESS_GRANTED, true),
            array(array('perm_foo'), null, VoterInterface::ACCESS_DENIED, false),
        );
    }

    /**
     * @dataProvider getVoteAttributes
     *
     * @param array     $attributes        The attributes
     * @param mixed     $subject           The subject
     * @param int       $result            The expected result
     * @param bool|null $permManagerResult The result of permission manager
     */
    public function testVote(array $attributes, $subject, $result, $permManagerResult = null)
    {
        $sids = array(
            new RoleSecurityIdentity(MockRole::class, 'ROLE_USER'),
        );

        if (null !== $permManagerResult) {
            $this->sidManager->expects($this->once())
                ->method('getSecurityIdentities')
                ->with($this->token)
                ->willReturn($sids);

            if (is_array($subject) && isset($subject[0]) && isset($subject[1])) {
                $this->permManager->expects($this->once())
                    ->method('isGranted')
                    ->with($sids, substr($attributes[0], 5), new FieldVote($subject[0], $subject[1]))
                    ->willReturn($permManagerResult);
            } else {
                $this->permManager->expects($this->once())
                    ->method('isGranted')
                    ->with($sids, substr($attributes[0], 5), $subject)
                    ->willReturn($permManagerResult);
            }
        }

        $this->assertSame($result, $this->voter->vote($this->token, $subject, $attributes));
    }
}
