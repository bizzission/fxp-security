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

use Sonatra\Component\Security\Event\GetExpressionVariablesEvent;
use Sonatra\Component\Security\ExpressionVoterEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Variable storage of expression voters.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ExpressionVoterVariableStorage implements EventSubscriberInterface
{
    /**
     * @var array<string, mixed>
     */
    private $variables = array();

    /**
     * Constructor.
     *
     * @param array<string, mixed> $variables The expression variables
     */
    public function __construct(array $variables = array())
    {
        foreach ($variables as $name => $value) {
            $this->addVariable($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ExpressionVoterEvents::GET_VARIABLES => array('setVariables', 0),
        );
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
     * Set the variables in event.
     *
     * @param GetExpressionVariablesEvent $event The event
     */
    public function setVariables(GetExpressionVariablesEvent $event)
    {
        $event->addVariables($this->variables);
    }
}
