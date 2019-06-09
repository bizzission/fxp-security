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

use Fxp\Component\Security\Sharing\Loader\LoaderInterface;

/**
 * Sharing factory.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SharingFactory implements SharingFactoryInterface
{
    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * Constructor.
     *
     * @param LoaderInterface $loader The sharing loader
     */
    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function createSubjectConfigurations(): array
    {
        return $this->mergeConfigs($this->loader->loadSubjectConfigurations());
    }

    /**
     * {@inheritdoc}
     */
    public function createIdentityConfigurations(): array
    {
        return $this->mergeConfigs($this->loader->loadIdentityConfigurations());
    }

    /**
     * Merge the sharing configurations.
     *
     * @param SharingIdentityConfigInterface[]|SharingSubjectConfigInterface[] $configs The configuration
     *
     * @return SharingIdentityConfigInterface[]|SharingSubjectConfigInterface[]
     */
    private function mergeConfigs(array $configs): array
    {
        $mergedConfigs = [];

        foreach ($configs as $config) {
            if (isset($mergedConfigs[$config->getType()])) {
                $mergedConfigs[$config->getType()]->merge($config);
            } else {
                $mergedConfigs[$config->getType()] = $config;
            }
        }

        return $mergedConfigs;
    }
}
