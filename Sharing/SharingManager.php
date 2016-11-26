<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Sharing;

use Doctrine\Common\Util\ClassUtils;
use Sonatra\Component\Security\Exception\AlreadyConfigurationAliasExistingException;
use Sonatra\Component\Security\Exception\SharingIdentityConfigNotFoundException;

/**
 * Sharing manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingManager implements SharingManagerInterface
{
    /**
     * @var array
     */
    protected $identityConfigs = array();

    /**
     * @var array
     */
    protected $identityAliases = array();

    /**
     * Constructor.
     *
     * @param SharingIdentityConfigInterface[] $identityConfigs The sharing configs
     */
    public function __construct(array $identityConfigs = array())
    {
        foreach ($identityConfigs as $config) {
            $this->addIdentityConfig($config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addIdentityConfig(SharingIdentityConfigInterface $config)
    {
        if (isset($this->identityAliases[$config->getAlias()])) {
            throw new AlreadyConfigurationAliasExistingException($config->getAlias(), $config->getType());
        }

        $this->identityConfigs[$config->getType()] = $config;
        $this->identityAliases[$config->getAlias()] = $config->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function hasIdentityConfig($class)
    {
        return isset($this->identityConfigs[ClassUtils::getRealClass($class)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityConfig($class)
    {
        $class = ClassUtils::getRealClass($class);

        if (!$this->hasIdentityConfig($class)) {
            throw new SharingIdentityConfigNotFoundException($class);
        }

        return $this->identityConfigs[$class];
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityConfigs()
    {
        return array_values($this->identityConfigs);
    }
}
