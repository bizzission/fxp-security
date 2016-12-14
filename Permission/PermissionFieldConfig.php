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

/**
 * Permission field config.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
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
     * Constructor.
     *
     * @param string   $field      The field name
     * @param string[] $operations The permission operations of this field
     */
    public function __construct($field,
                                array $operations = array())
    {
        $this->field = $field;
        $this->operations = array_values($operations);
    }

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOperation($operation)
    {
        return in_array($operation, $this->operations);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperations()
    {
        return $this->operations;
    }
}
