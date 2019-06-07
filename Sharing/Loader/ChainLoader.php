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

use Fxp\Component\Security\Exception\LoaderException;

/**
 * Sharing chain loader.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ChainLoader implements LoaderInterface
{
    /**
     * @var LoaderInterface[]
     */
    protected $loaders;

    /**
     * Constructor.
     *
     * @param LoaderInterface[] $loaders The sharing loaders to use
     *
     * @throws LoaderException If any of the loaders has an invalid type
     */
    public function __construct(array $loaders)
    {
        foreach ($loaders as $loader) {
            if (!$loader instanceof LoaderInterface) {
                throw new LoaderException(sprintf('Class %s is expected to implement LoaderInterface', \get_class($loader)));
            }
        }

        $this->loaders = $loaders;
    }

    /**
     * {@inheritdoc}
     */
    public function loadSubjectConfigurations(): array
    {
        $configs = [];

        foreach ($this->loaders as $loader) {
            $configs[] = $loader->loadSubjectConfigurations();
        }

        return \count($configs) > 0 ? array_merge(...$configs) : [];
    }

    /**
     * {@inheritdoc}
     */
    public function loadIdentityConfigurations(): array
    {
        $configs = [];

        foreach ($this->loaders as $loader) {
            $configs[] = $loader->loadIdentityConfigurations();
        }

        return \count($configs) > 0 ? array_merge(...$configs) : [];
    }
}
