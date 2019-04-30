<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Fixtures\Model;

use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\OrganizationUserInterface;
use Fxp\Component\Security\Model\Traits\OrganizationUserTrait;
use Fxp\Component\Security\Model\UserInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MockOrganizationUser implements OrganizationUserInterface
{
    use OrganizationUserTrait;

    /**
     * @var null|int
     */
    protected $id;

    /**
     * Constructor.
     *
     * @param OrganizationInterface $organization The organization
     * @param UserInterface         $user         The user
     * @param int                   $id           The id
     */
    public function __construct(OrganizationInterface $organization, UserInterface $user, $id = 42)
    {
        $this->organization = $organization;
        $this->user = $user;
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }
}
