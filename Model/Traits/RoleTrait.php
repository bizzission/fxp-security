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

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait for role model.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
trait RoleTrait
{
    use PermissionsTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $name;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->name;
    }
}
