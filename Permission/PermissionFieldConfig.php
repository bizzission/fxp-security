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

/**
 * Permission field config.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionFieldConfig implements PermissionFieldConfigInterface
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var string[]
     */
    protected $operations;

    /**
     * @var string[]
     */
    protected $mappingPermissions;

    /**
     * @var null|bool
     */
    protected $editable;

    /**
     * Constructor.
     *
     * @param string    $field              The field name
     * @param string[]  $operations         The permission operations of this field
     * @param string[]  $mappingPermissions The map of alias permission and real permission
     * @param null|bool $editable           Check if the permission is editable
     */
    public function __construct(
        string $field,
        array $operations = [],
        array $mappingPermissions = [],
        ?bool $editable = null
    ) {
        $this->field = $field;
        $this->operations = array_values($operations);
        $this->mappingPermissions = $mappingPermissions;
        $this->editable = $editable;
    }

    /**
     * {@inheritdoc}
     */
    public function getField(): string
    {
        return $this->field;
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
    public function isEditable(): bool
    {
        return null !== $this->editable ? (bool) $this->editable : empty($this->getOperations());
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
}
