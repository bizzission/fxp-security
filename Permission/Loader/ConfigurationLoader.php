<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Permission\Loader;

use Fxp\Component\Security\Permission\PermissionConfigInterface;

/**
 * Permission configuration loader.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConfigurationLoader implements LoaderInterface
{
    /**
     * @var PermissionConfigInterface[]
     */
    protected $configs;

    /**
     * Constructor.
     *
     * @param PermissionConfigInterface[] $configs The permission configs
     */
    public function __construct(array $configs = [])
    {
        $this->configs = $configs;
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfigurations(): array
    {
        return $this->configs;
    }
}
