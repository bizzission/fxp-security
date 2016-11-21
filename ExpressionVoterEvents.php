<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class ExpressionVoterEvents
{
    /**
     * The GET_VARIABLES event occurs when the expression voter try to get the global variables.
     *
     * @Event("Sonatra\Component\Security\Event\GetExpressionVariablesEvent")
     *
     * @var string
     */
    const GET_VARIABLES = 'sonatra_security.expression_voter.get_variables';
}
