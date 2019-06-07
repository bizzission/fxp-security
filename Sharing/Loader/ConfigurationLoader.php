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
use Fxp\Component\Security\Sharing\SharingIdentityConfigInterface;
use Fxp\Component\Security\Sharing\SharingSubjectConfigInterface;

/**
 * Sharing configuration loader.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConfigurationLoader implements LoaderInterface
{
    /**
     * @var SharingSubjectConfigInterface[]
     */
    protected $subjectConfigs;

    /**
     * @var SharingIdentityConfigInterface[]
     */
    protected $identityConfigs;

    /**
     * Constructor.
     *
     * @param SharingSubjectConfigInterface[]  $subjectConfigs  The subject configs
     * @param SharingIdentityConfigInterface[] $identityConfigs The identity configs
     *
     * @throws LoaderException If any of the loaders has an invalid type
     */
    public function __construct(array $subjectConfigs = [], array $identityConfigs = [])
    {
        $this->subjectConfigs = $subjectConfigs;
        $this->identityConfigs = $identityConfigs;
    }

    /**
     * {@inheritdoc}
     */
    public function loadSubjectConfigurations(): array
    {
        return $this->subjectConfigs;
    }

    /**
     * {@inheritdoc}
     */
    public function loadIdentityConfigurations(): array
    {
        return $this->identityConfigs;
    }
}
