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

use Fxp\Component\Security\Loader\AbstractCacheLoader;
use Fxp\Component\Security\Sharing\SharingIdentityConfigInterface;
use Fxp\Component\Security\Sharing\SharingSubjectConfigInterface;

/**
 * Sharing chain loader.
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
     * @var null|SharingSubjectConfigInterface[]
     */
    protected $subjectConfigs;

    /**
     * @var null|SharingIdentityConfigInterface[]
     */
    protected $identityConfigs;

    /**
     * Constructor.
     *
     * @param LoaderInterface $loader  The sharing configurations loader
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
    public function loadSubjectConfigurations(): array
    {
        if (null === $this->subjectConfigs) {
            if (null === $this->options['cache_dir'] || $this->options['debug']) {
                $this->subjectConfigs = $this->loader->loadSubjectConfigurations();
            } else {
                $this->subjectConfigs = $this->loadConfigurationFromCache('sharing_subject', function () {
                    return $this->loader->loadSubjectConfigurations();
                });
            }
        }

        return $this->subjectConfigs;
    }

    /**
     * {@inheritdoc}
     */
    public function loadIdentityConfigurations(): array
    {
        if (null === $this->identityConfigs) {
            if (null === $this->options['cache_dir'] || $this->options['debug']) {
                $this->identityConfigs = $this->loader->loadIdentityConfigurations();
            } else {
                $this->identityConfigs = $this->loadConfigurationFromCache('sharing_identity', function () {
                    return $this->loader->loadIdentityConfigurations();
                });
            }
        }

        return $this->identityConfigs;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir): void
    {
        // skip warmUp when sharing manager doesn't use cache
        if (null === $this->options['cache_dir']) {
            return;
        }

        $this->subjectConfigs = null;
        $this->identityConfigs = null;

        $this->loadSubjectConfigurations();
        $this->loadIdentityConfigurations();
    }
}
