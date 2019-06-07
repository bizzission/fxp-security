<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Fixtures\Loader;

use Fxp\Component\Security\Loader\AbstractCacheLoader;
use Symfony\Component\Config\ConfigCacheFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MockCacheLoader extends AbstractCacheLoader
{
    public function warmUp($cacheDir): void
    {
        $this->getConfigCacheFactory();
    }

    public function getProtectedConfigCacheFactory(): ?ConfigCacheFactoryInterface
    {
        return $this->configCacheFactory;
    }
}
