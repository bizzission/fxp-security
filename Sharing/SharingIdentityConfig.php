<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Sharing;

/**
 * Sharing identity config.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SharingIdentityConfig implements SharingIdentityConfigInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var bool
     */
    protected $roleable;

    /**
     * @var bool
     */
    protected $permissible;

    /**
     * Constructor.
     *
     * @param string      $type        The type, typically, this is the PHP class name
     * @param null|string $alias       The alias of identity type
     * @param bool        $roleable    Check if the identity can be use the roles
     * @param bool        $permissible Check if the identity can be use the permissions
     */
    public function __construct(string $type, ?string $alias = null, bool $roleable = false, bool $permissible = false)
    {
        $this->type = $type;
        $this->alias = $this->buildAlias($type, $alias);
        $this->roleable = $roleable;
        $this->permissible = $permissible;
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
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * {@inheritdoc}
     */
    public function isRoleable(): bool
    {
        return $this->roleable;
    }

    /**
     * {@inheritdoc}
     */
    public function isPermissible(): bool
    {
        return $this->permissible;
    }

    /**
     * Build the alias.
     *
     * @param string      $classname The class name
     * @param null|string $alias     The alias
     *
     * @return string
     */
    private function buildAlias(string $classname, ?string $alias): string
    {
        return $alias ?? strtolower(substr(strrchr($classname, '\\'), 1));
    }
}
