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

use Sonatra\Component\Security\Authorization\Voter\RoleSecurityIdentityVoter;
use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class RoleSecurityIdentityVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SecurityIdentityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidManager;

    /**
     * @var RoleSecurityIdentityVoter
     */
    protected $voter;

    protected function setUp()
    {
        $this->sidManager = $this->getMockBuilder(SecurityIdentityManagerInterface::class)->getMock();
        $this->voter = new RoleSecurityIdentityVoter($this->sidManager, 'TEST_');
    }

    public function getAccessResults()
    {
        return array(
            array(array('TEST_ADMIN'), VoterInterface::ACCESS_GRANTED),
            array(array('TEST_USER'), VoterInterface::ACCESS_DENIED),
            array(array('ROLE_ADMIN'), VoterInterface::ACCESS_ABSTAIN),
        );
    }

    /**
     * @dataProvider getAccessResults
     *
     * @param string[] $attributes The voter attributes
     * @param int      $access     The access status of voter
     */
    public function testExtractRolesWithAccessGranted(array $attributes, $access)
    {
        /* @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject $token */
        $token = $this->getMockBuilder(TokenInterface::class)->getMock();

        $sids = array(
            new RoleSecurityIdentity('TEST_ADMIN'),
        );

        $this->sidManager->expects($this->atLeast(2))
            ->method('getSecurityIdentities')
            ->willReturn($sids);

        $this->assertSame($access, $this->voter->vote($token, null, $attributes));
        $this->assertSame($access, $this->voter->vote($token, null, $attributes));
    }
}
