<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Model\Traits;

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Security\Model\OrganizationInterface;
use Sonatra\Component\Security\Model\Traits\OrganizationalRequiredTrait;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrganizationalRequiredTraitTest extends TestCase
{
    public function testModel()
    {
        /* @var OrganizationInterface $org */
        $org = $this->getMockBuilder(OrganizationInterface::class)->getMock();

        /* @var OrganizationalRequiredTrait $model */
        $model = $this->getMockForTrait(OrganizationalRequiredTrait::class);
        $model->setOrganization($org);

        $this->assertSame($org, $model->getOrganization());
    }
}
