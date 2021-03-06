<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Model\Traits;

use Fxp\Component\Security\Model\OrganizationInterface;

/**
 * Trait to indicate that the model is linked with an optional organization.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
trait OrganizationalOptionalTrait
{
    use OrganizationalTrait;

    /**
     * {@inheritdoc}
     */
    public function setOrganization(?OrganizationInterface $organization): self
    {
        $this->organization = $organization;

        return $this;
    }
}
