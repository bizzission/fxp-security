<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Permission;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Permission config Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface PermissionConfigInterface
{
    /**
     * Get the type. Typically, this is the PHP class name.
     *
     * @return string
     */
    public function getType();

    /**
     * Check if the operation is defined.
     *
     * @param string $operation The operation name
     *
     * @return bool
     */
    public function hasOperation($operation);

    /**
     * Get the available operations for this type.
     *
     * @return string[]
     */
    public function getOperations();

    /**
     * Check if the field configuration exists.
     *
     * @param string $field The field name
     *
     * @return bool
     */
    public function hasField($field);

    /**
     * Get the field configuration.
     *
     * @param string $field The field name
     *
     * @return PermissionFieldConfigInterface|null
     */
    public function getField($field);

    /**
     * Get the available fields.
     *
     * @return PermissionFieldConfigInterface[]
     */
    public function getFields();

    /**
     * Get the master relation of permission.
     *
     * @return PropertyPathInterface|string|null
     */
    public function getMaster();

    /**
     * Get the map of the permission of master type with the field permission of this type.
     *
     * Example: [
     *     'view' => 'read',
     *     'create' => 'edit',
     *     'update' => 'edit',
     * ]
     *
     * @return array
     */
    public function getMasterFieldMappingPermissions();
}
