<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Model\Traits;

use Sonatra\Component\Security\Model\OrganizationInterface;

/**
 * Organizational interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface OrganizationalInterface
{
    /**
     * Get the organization.
     *
     * @return OrganizationInterface|null
     */
    public function getOrganization();
}
