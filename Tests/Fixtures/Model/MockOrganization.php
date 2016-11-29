<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Fixtures\Model;

use Sonatra\Component\Security\Model\Organization;
use Sonatra\Component\Security\Model\Traits\OrganizationGroupsInterface;
use Sonatra\Component\Security\Model\Traits\OrganizationGroupsTrait;
use Sonatra\Component\Security\Model\Traits\OrganizationRolesInterface;
use Sonatra\Component\Security\Model\Traits\OrganizationRolesTrait;
use Sonatra\Component\Security\Model\Traits\RoleableInterface;
use Sonatra\Component\Security\Model\Traits\RoleableTrait;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MockOrganization extends Organization implements
    RoleableInterface,
    OrganizationRolesInterface,
    OrganizationGroupsInterface
{
    use RoleableTrait;
    use OrganizationRolesTrait;
    use OrganizationGroupsTrait;

    /**
     * Constructor.
     *
     * @param string $name The unique name
     * @param int    $id   The id
     */
    public function __construct($name, $id = 23)
    {
        parent::__construct($name);

        $this->id = $id;
    }
}
