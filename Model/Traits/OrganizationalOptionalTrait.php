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

/**
 * Trait to indicate that the model is linked with an optional organization.
 *
 * @author François Pluchino <francois.pluchino@helloguest.com>
 */
trait OrganizationalOptionalTrait
{
    use OrganizationalTrait;

    /**
     * {@inheritdoc}
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }
}
