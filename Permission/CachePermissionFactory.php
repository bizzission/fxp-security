<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Permission;

use Fxp\Component\Security\Cache\AbstractCache;

/**
 * Cache permission factory.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CachePermissionFactory extends AbstractCache implements PermissionFactoryInterface
{
    /**
     * @var PermissionFactoryInterface
     */
    protected $factory;

    /**
     * Constructor.
     *
     * @param PermissionFactoryInterface $factory The permission factory
     * @param array                      $options An array of options
     */
    public function __construct(PermissionFactoryInterface $factory, array $options = [])
    {
        parent::__construct($options);

        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function createConfigurations(): array
    {
        if (null === $this->options['cache_dir'] || $this->options['debug']) {
            return $this->factory->createConfigurations();
        }

        return $this->loadConfigurationFromCache('permission', function () {
            return $this->factory->createConfigurations();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir): void
    {
        // skip warmUp when the config doesn't use cache
        if (null === $this->options['cache_dir']) {
            return;
        }

        $this->createConfigurations();
    }
}
