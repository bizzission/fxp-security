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
 * PermissionNotFoundException for the Security component.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionNotFoundException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * Constructor.
     *
     * @param string      $operation The permission operation
     * @param string      $class     The class name
     * @param string|null $field     The field name
     */
    public function __construct($operation, $class, $field = null)
    {
        $msg = 'The permission "%s" for "%s%s" is not found ant it required by the permission configuration';
        $msg = sprintf($msg, $operation, $class, null === $field ? '' : '::'.$field);

        parent::__construct($msg);
    }
}
