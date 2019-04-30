<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Model;

/**
 * Permission interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface PermissionInterface
{
    /**
     * Get the id.
     *
     * @return null|int|string
     */
    public function getId();

    /**
     * Set the operation.
     *
     * @param null|string $operation The operation
     *
     * @return self
     */
    public function setOperation($operation);

    /**
     * Get the operation.
     *
     * @return null|string
     */
    public function getOperation();

    /**
     * Set the permission contexts.
     *
     * @param string[] $contexts The permission contexts
     *
     * @return self
     */
    public function setContexts(array $contexts);

    /**
     * Get the permission contexts.
     *
     * @return string[]
     */
    public function getContexts();

    /**
     * Set the classname.
     *
     * @param null|string $class The classname
     *
     * @return self
     */
    public function setClass($class);

    /**
     * Get the classname.
     *
     * @return null|string
     */
    public function getClass();

    /**
     * Set the field.
     *
     * @param null|string $field The field
     *
     * @return self
     */
    public function setField($field);

    /**
     * Get the field.
     *
     * @return null|string
     */
    public function getField();
}
