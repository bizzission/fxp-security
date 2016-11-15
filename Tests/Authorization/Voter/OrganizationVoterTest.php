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

use Sonatra\Component\Security\Authorization\Voter\OrganizationVoter;
use Sonatra\Component\Security\Identity\OrganizationSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrganizationVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SecurityIdentityRetrievalStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sidStrategy;

    /**
     * @var OrganizationVoter
     */
    protected $voter;

    protected function setUp()
    {
        $this->sidStrategy = $this->getMockBuilder(SecurityIdentityRetrievalStrategyInterface::class)->getMock();
        $this->voter = new OrganizationVoter($this->sidStrategy, null);
    }

    public function getAccessResults()
    {
        return array(
            array(array('ORG_FOO'), VoterInterface::ACCESS_GRANTED),
            array(array('ORG_BAR'), VoterInterface::ACCESS_DENIED),
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
            new OrganizationSecurityIdentity('FOO'),
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
