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
use Fxp\Component\Security\Annotation\SharingSubject;
use Fxp\Component\Security\Sharing\SharingSubjectConfig;
use Fxp\Component\Security\Sharing\SharingSubjectConfigCollection;
use Symfony\Component\Config\Resource\DirectoryResource;

/**
 * Sharing subject annotation loader.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SubjectAnnotationLoader extends AbstractAnnotationLoader
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
    public function load($resource, $type = null): SharingSubjectConfigCollection
    {
        $configs = new SharingSubjectConfigCollection();
        $configs->addResource(new DirectoryResource($resource));

        foreach ($this->classFinder->findClasses([$resource]) as $class) {
            try {
                $refClass = new \ReflectionClass($class);
                $classAnnotations = $this->reader->getClassAnnotations($refClass);

                foreach ($classAnnotations as $annotation) {
                    if ($annotation instanceof SharingSubject) {
                        $configs->add(new SharingSubjectConfig(
                            $class,
                            $annotation->getVisibility()
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
