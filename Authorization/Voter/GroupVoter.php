<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Authorization\Voter;

use Fxp\Component\Security\Model\GroupInterface;

/**
 * Voter to determine the groups granted on current user defined in token.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class GroupVoter extends AbstractIdentityVoter
{
    /**
     * {@inheritdoc}
     */
    protected function getValidType(): string
    {
        return GroupInterface::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultPrefix(): string
    {
        return 'GROUP_';
    }
}
