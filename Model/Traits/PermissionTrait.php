<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Model\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Fxp\Component\Security\Model\RoleInterface;

/**
 * Trait of permission model.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
trait PermissionTrait
{
    /**
     * @var string[]
     *
     * @ORM\Column(type="array")
     */
    protected $contexts = [];

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $class;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $field;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $operation;

    /**
     * @var Collection|RoleInterface[]|null
     *
     * @ORM\ManyToMany(targetEntity="Fxp\Component\Security\Model\RoleInterface", mappedBy="permissions")
     */
    protected $roles;

    /**
     * {@inheritdoc}
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * {@inheritdoc}
     */
    public function setContexts(array $contexts)
    {
        $this->contexts = $contexts;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContexts()
    {
        return $this->contexts;
    }

    /**
     * {@inheritdoc}
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
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
    public function getRoles()
    {
        return $this->roles ?: $this->roles = new ArrayCollection();
    }
}
