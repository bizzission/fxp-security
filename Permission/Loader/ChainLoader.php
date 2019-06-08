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

use Fxp\Component\Security\Exception\LoaderException;

/**
 * Permission chain loader.
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
     * @param LoaderInterface[] $loaders The permission loaders to use
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
    public function loadConfigurations(): array
    {
        $configs = [];

        foreach ($this->loaders as $loader) {
            $configs[] = $loader->loadConfigurations();
        }

        return \count($configs) > 0 ? array_merge(...$configs) : [];
    }
}
