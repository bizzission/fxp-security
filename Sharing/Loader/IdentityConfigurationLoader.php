<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Sharing\Loader;

use Fxp\Component\Security\Sharing\SharingIdentityConfigCollection;
use Fxp\Component\Security\Sharing\SharingIdentityConfigInterface;
use Symfony\Component\Config\Loader\Loader;

/**
 * Sharing identity configuration loader.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class IdentityConfigurationLoader extends Loader
{
    /**
     * @var SharingIdentityConfigCollection
     */
    protected $configs;

    /**
     * Constructor.
     *
     * @param SharingIdentityConfigInterface[] $configs The sharing identity configs
     */
    public function __construct(array $configs = [])
    {
        $this->configs = new SharingIdentityConfigCollection();

        foreach ($configs as $config) {
            $this->configs->add($config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null): SharingIdentityConfigCollection
    {
        return $this->configs;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null): bool
    {
        return 'config' === $type;
    }
}
