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

use Fxp\Component\Security\Loader\AbstractCacheLoader;
use Fxp\Component\Security\Permission\PermissionConfigInterface;

/**
 * Permission chain loader.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CacheLoader extends AbstractCacheLoader implements LoaderInterface
{
    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var null|PermissionConfigInterface[]
     */
    protected $configs;

    /**
     * Constructor.
     *
     * @param LoaderInterface $loader  The permission configurations loader
     * @param array           $options An array of options
     */
    public function __construct(LoaderInterface $loader, array $options = [])
    {
        parent::__construct($options);

        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfigurations(): array
    {
        if (null === $this->configs) {
            if (null === $this->options['cache_dir'] || $this->options['debug']) {
                $this->configs = $this->loader->loadConfigurations();
            } else {
                $this->configs = $this->loadConfigurationFromCache('permission', function () {
                    return $this->loader->loadConfigurations();
                });
            }
        }

        return $this->configs;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir): void
    {
        // skip warmUp when permission manager doesn't use cache
        if (null === $this->options['cache_dir']) {
            return;
        }

        $this->configs = null;

        $this->loadConfigurations();
    }
}
