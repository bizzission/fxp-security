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
final class ExpressionVariableEvents
{
    /**
     * The GET event occurs when a service try to get the global variables.
     *
     * @Event("Sonatra\Component\Security\Event\GetExpressionVariablesEvent")
     *
     * @var string
     */
    const GET = 'sonatra_security.expression.get_variables';
}
