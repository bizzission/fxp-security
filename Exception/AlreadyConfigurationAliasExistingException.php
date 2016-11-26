<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Exception;

/**
 * AlreadyConfigurationAliasExistingException for the Security component.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AlreadyConfigurationAliasExistingException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * Constructor.
     *
     * @param string $alias The alias
     * @param string $class The class name
     */
    public function __construct($alias, $class)
    {
        parent::__construct(sprintf('The alias "%s" of sharing identity configuration for the class "%s" already exist', $alias, $class));
    }
}
