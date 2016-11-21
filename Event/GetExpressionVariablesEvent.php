<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * The get expression variables event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GetExpressionVariablesEvent extends Event
{
    /**
     * @var array<string, mixed>
     */
    protected $variables;

    /**
     * Constructor.
     *
     * @param array<string, mixed> $variables The variables
     */
    public function __construct(array $variables = array())
    {
        $this->variables = $variables;
    }

    /**
     * Add variables in the expression language evaluate variables.
     *
     * @param array<string, mixed> $variables The variables
     */
    public function addVariables(array $variables)
    {
        foreach ($variables as $name => $value) {
            $this->addVariable($name, $value);
        }
    }

    /**
     * Add a variable in the expression language evaluate variables.
     *
     * @param string $name  The name of expression variable
     * @param mixed  $value The value of expression variable
     */
    public function addVariable($name, $value)
    {
        $this->variables[$name] = $value;
    }

    /**
     * Get the variables.
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
