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

use Sonatra\Component\Security\SharingTypes;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Permission config Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionConfig implements PermissionConfigInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $sharingType = SharingTypes::TYPE_PUBLIC;

    /**
     * @var string[]
     */
    protected $fields = array();

    /**
     * @var PropertyPathInterface|string|null
     */
    protected $master;

    /**
     * Constructor.
     *
     * @param string   $type        The type, typically, this is the PHP class name
     * @param string[] $fields      The fields
     * @param string   $sharingType The sharing type
     * @param null     $master      The property path of master
     */
    public function __construct($type,
                                array $fields = array(),
                                $sharingType = SharingTypes::TYPE_PUBLIC,
                                $master = null)
    {
        $this->type = $type;
        $this->fields = $fields;
        $this->sharingType = $sharingType;
        $this->master = $master;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getSharingType()
    {
        return $this->sharingType;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
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
}
