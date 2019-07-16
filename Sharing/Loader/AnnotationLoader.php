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

use Fxp\Component\Config\Loader\AbstractAnnotationLoader;
use Fxp\Component\Security\Annotation\SharingIdentity;
use Fxp\Component\Security\Annotation\SharingSubject;
use Fxp\Component\Security\Sharing\SharingIdentityConfig;
use Fxp\Component\Security\Sharing\SharingIdentityConfigInterface;
use Fxp\Component\Security\Sharing\SharingSubjectConfig;
use Fxp\Component\Security\Sharing\SharingSubjectConfigInterface;

/**
 * Sharing annotation loader.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AnnotationLoader extends AbstractAnnotationLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadSubjectConfigurations(): array
    {
        return $this->loadConfigurations('getSubjectConfigurations');
    }

    /**
     * {@inheritdoc}
     */
    public function loadIdentityConfigurations(): array
    {
        return $this->loadConfigurations('getIdentityConfigurations');
    }

    /**
     * Load the configuration by action.
     *
     * @param string $action The method name
     *
     * @return array
     */
    protected function loadConfigurations(string $action): array
    {
        $configs = [];

        foreach ($this->classFinder->findClasses() as $class) {
            try {
                $refClass = new \ReflectionClass($class);
                $configs = $this->{$action}($refClass, $configs);
            } catch (\ReflectionException $e) {
                // skip
            }
        }

        return array_values($configs);
    }

    /**
     * Get the subject configurations.
     *
     * @param \ReflectionClass                $refClass The reflection class
     * @param SharingSubjectConfigInterface[] $configs  The map of subject config
     *
     * @return SharingSubjectConfigInterface[]
     */
    protected function getSubjectConfigurations(\ReflectionClass $refClass, array $configs): array
    {
        $class = $refClass->name;
        $classAnnotations = $this->reader->getClassAnnotations($refClass);

        foreach ($classAnnotations as $annotation) {
            if ($annotation instanceof SharingSubject) {
                $config = new SharingSubjectConfig(
                    $class,
                    $annotation->getVisibility()
                );

                if (isset($configs[$class])) {
                    $configs[$class]->merge($config);
                } else {
                    $configs[$class] = $config;
                }
            }
        }

        return $configs;
    }

    /**
     * Get the permission configurations.
     *
     * @param \ReflectionClass                 $refClass The reflection class
     * @param SharingIdentityConfigInterface[] $configs  The map of permission config
     *
     * @return SharingIdentityConfigInterface[]
     */
    protected function getIdentityConfigurations(\ReflectionClass $refClass, array $configs): array
    {
        $class = $refClass->name;
        $classAnnotations = $this->reader->getClassAnnotations($refClass);

        foreach ($classAnnotations as $annotation) {
            if ($annotation instanceof SharingIdentity) {
                $config = new SharingIdentityConfig(
                    $class,
                    $annotation->getAlias(),
                    $annotation->getRoleable(),
                    $annotation->getPermissible()
                );

                if (isset($configs[$class])) {
                    $configs[$class]->merge($config);
                } else {
                    $configs[$class] = $config;
                }
            }
        }

        return $configs;
    }
}
