<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Core\Authorization\Voter;

use Sonatra\Component\Security\Model\GroupInterface;

/**
 * GroupableVoter to determine the groups granted on current user defined in token.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GroupableVoter extends AbstractIdentityVoter
{
    /**
     * {@inheritdoc}
     */
    protected function getValidClass()
    {
        return GroupInterface::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultPrefix()
    {
        return 'GROUP_';
    }
}
