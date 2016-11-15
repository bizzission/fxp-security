<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Authorization\Voter;

use Sonatra\Component\Security\Identity\OrganizationSecurityIdentity;

/**
 * OrganizationVoter to determine the organization granted on current user defined in token.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrganizationVoter extends AbstractIdentityVoter
{
    /**
     * {@inheritdoc}
     */
    protected function getValidType()
    {
        return OrganizationSecurityIdentity::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultPrefix()
    {
        return 'ORG_';
    }
}
