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

use Fxp\Component\Config\ConfigCollectionInterface;
use Fxp\Component\Config\Loader\AbstractArrayResourceLoader;
use Fxp\Component\Security\Sharing\SharingSubjectConfigCollection;

/**
 * Sharing subject array resource loader.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SubjectArrayResourceLoader extends AbstractArrayResourceLoader
{
    /**
     * {@inheritdoc}
     *
     * @return ConfigCollectionInterface|SharingSubjectConfigCollection
     */
    public function load($resource, $type = null): SharingSubjectConfigCollection
    {
        return parent::load($resource, $type);
    }

    /**
     * {@inheritdoc}
     */
    protected function createConfigCollection(): ConfigCollectionInterface
    {
        return new SharingSubjectConfigCollection();
    }
}
