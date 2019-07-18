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
use Fxp\Component\Security\Sharing\SharingIdentityConfig;
use Fxp\Component\Security\Sharing\SharingIdentityConfigCollection;
use Symfony\Component\Config\Resource\DirectoryResource;

/**
 * Sharing identity annotation loader.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class IdentityAnnotationLoader extends AbstractAnnotationLoader
{
    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null): bool
    {
        return 'annotation' === $type && \is_string($resource) && is_dir($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null): SharingIdentityConfigCollection
    {
        $configs = new SharingIdentityConfigCollection();
        $configs->addResource(new DirectoryResource($resource));

        foreach ($this->classFinder->findClasses([$resource]) as $class) {
            try {
                $refClass = new \ReflectionClass($class);
                $classAnnotations = $this->reader->getClassAnnotations($refClass);

                foreach ($classAnnotations as $annotation) {
                    if ($annotation instanceof SharingIdentity) {
                        $configs->add(new SharingIdentityConfig(
                            $class,
                            $annotation->getAlias(),
                            $annotation->getRoleable(),
                            $annotation->getPermissible()
                        ));
                    }
                }
            } catch (\ReflectionException $e) {
                // skip
            }
        }

        return $configs;
    }
}
