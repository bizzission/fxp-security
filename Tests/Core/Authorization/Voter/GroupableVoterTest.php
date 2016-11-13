<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Core\Authorization\Voter;

use Sonatra\Component\Security\Core\Authorization\Voter\GroupableVoter;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockGroup;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\SecurityIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GroupableVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SecurityIdentityRetrievalStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidStrategy;

    /**
     * @var GroupableVoter
     */
    protected $voter;

    protected function setUp()
    {
        $this->sidStrategy = $this->getMockBuilder(SecurityIdentityRetrievalStrategyInterface::class)->getMock();
        $this->voter = new GroupableVoter($this->sidStrategy, null);
    }

    public function getAccessResults()
    {
        return array(
            array(array('GROUP_FOO'), VoterInterface::ACCESS_GRANTED),
            array(array('GROUP_BAR'), VoterInterface::ACCESS_DENIED),
            array(array('TEST_FOO'), VoterInterface::ACCESS_ABSTAIN),
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
            new UserSecurityIdentity('FOO', MockGroup::class),
        );

        if (VoterInterface::ACCESS_ABSTAIN !== $access) {
            $this->sidStrategy->expects($this->atLeast(2))
                ->method('getSecurityIdentities')
                ->willReturn($sids);
        }

        $this->assertSame($access, $this->voter->vote($token, null, $attributes));
        $this->assertSame($access, $this->voter->vote($token, null, $attributes));
    }
}
