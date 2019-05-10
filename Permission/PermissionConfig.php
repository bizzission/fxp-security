<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Permission;

use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Permission config.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionConfig implements PermissionConfigInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string[]
     */
    protected $operations;

    /**
     * @var string[]
     */
    protected $mappingPermissions;

    /**
     * @var PermissionFieldConfigInterface[]
     */
    protected $fields = [];

    /**
     * @var null|PropertyPathInterface|string
     */
    protected $master;

    /**
     * @var array
     */
    protected $masterFieldMappingPermissions;

    /**
     * Constructor.
     *
     * @param string                            $type                          The type, typically, this is the PHP class name
     * @param string[]                          $operations                    The permission operations of this type
     * @param string[]                          $mappingPermissions            The map of alias permission and real permission
     * @param PermissionFieldConfigInterface[]  $fields                        The field configurations
     * @param null|PropertyPathInterface|string $master                        The property path of master
     * @param array[]                           $masterFieldMappingPermissions The map of field permission of this type with the permission of master type
     */
    public function __construct(
        $type,
        array $operations = [],
        array $mappingPermissions = [],
        array $fields = [],
        $master = null,
        array $masterFieldMappingPermissions = []
    ) {
        $this->type = $type;
        $this->operations = array_values($operations);
        $this->mappingPermissions = $mappingPermissions;
        $this->master = $master;
        $this->masterFieldMappingPermissions = $masterFieldMappingPermissions;

        foreach ($fields as $field) {
            $this->addField($field);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOperation(string $operation): bool
    {
        return \in_array($this->getMappingPermission($operation), $this->operations, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * {@inheritdoc}
     */
    public function hasField(string $field): bool
    {
        return isset($this->fields[$field]);
    }

    /**
     * {@inheritdoc}
     */
    public function getField(string $field): ?PermissionFieldConfigInterface
    {
        return $this->fields[$field] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * {@inheritdoc}
     */
    public function getMasterFieldMappingPermissions(): array
    {
        return $this->masterFieldMappingPermissions;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingPermission(string $aliasPermission): string
    {
        return $this->mappingPermissions[$aliasPermission] ?? $aliasPermission;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingPermissions(): array
    {
        return $this->mappingPermissions;
    }

    /**
     * Add the permission field configuration.
     *
     * @param PermissionFieldConfigInterface $fieldConfig The permission field configuration
     *
     * @return static
     */
    private function addField(PermissionFieldConfigInterface $fieldConfig): PermissionConfig
    {
        $this->fields[$fieldConfig->getField()] = $fieldConfig;

        return $this;
    }
}
