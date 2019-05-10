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
     * @var null|string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $name;

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return (string) $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(?string $name = null): self
    {
        $this->name = $name;

        return $this;
    }
}
