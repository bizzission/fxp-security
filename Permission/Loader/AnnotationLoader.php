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

use Fxp\Component\Config\Loader\AbstractAnnotationLoader;
use Fxp\Component\Security\Annotation\Permission;
use Fxp\Component\Security\Annotation\PermissionField;
use Fxp\Component\Security\Permission\PermissionConfig;
use Fxp\Component\Security\Permission\PermissionConfigCollection;
use Fxp\Component\Security\Permission\PermissionFieldConfig;
use Fxp\Component\Security\Permission\PermissionFieldConfigInterface;
use Symfony\Component\Config\Resource\DirectoryResource;

/**
 * Permission annotation loader.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AnnotationLoader extends AbstractAnnotationLoader
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
    public function load($resource, $type = null): PermissionConfigCollection
    {
        $configs = new PermissionConfigCollection();
        $configs->addResource(new DirectoryResource($resource));

        foreach ($this->classFinder->findClasses([$resource]) as $class) {
            try {
                $refClass = new \ReflectionClass($class);
                $configs = $this->getConfigurations($refClass, $configs);

                if (!empty($fieldConfigurations = $this->getFieldConfigurations($refClass))) {
                    $configs->add(new PermissionConfig($class, [], [], $fieldConfigurations));
                }
            } catch (\ReflectionException $e) {
                // skip
            }
        }

        return $configs;
    }

    /**
     * Get the permission configurations.
     *
     * @param \ReflectionClass           $refClass The reflection class
     * @param PermissionConfigCollection $configs  The permission config collection
     *
     * @return PermissionConfigCollection
     */
    private function getConfigurations(\ReflectionClass $refClass, PermissionConfigCollection $configs): PermissionConfigCollection
    {
        $classAnnotations = $this->reader->getClassAnnotations($refClass);

        foreach ($classAnnotations as $annotation) {
            if ($annotation instanceof Permission) {
                $configs->add(new PermissionConfig(
                    $refClass->name,
                    $annotation->getOperations(),
                    $annotation->getMappingPermissions(),
                    $this->convertPermissionFields($annotation->getFields()),
                    $annotation->getMaster(),
                    $annotation->getMasterFieldMappingPermissions(),
                    $annotation->getBuildFields(),
                    $annotation->getBuildDefaultFields()
                ));
            }
        }

        return $configs;
    }

    /**
     * Get the permission field configuration.
     *
     * @param \ReflectionClass $refClass The reflection class
     *
     * @return PermissionFieldConfigInterface[]
     */
    private function getFieldConfigurations(\ReflectionClass $refClass): array
    {
        $configs = [];

        foreach ($refClass->getProperties() as $refProperty) {
            $propertyAnnotations = $this->reader->getPropertyAnnotations($refProperty);
            $field = $refProperty->name;

            foreach ($propertyAnnotations as $annotation) {
                if ($annotation instanceof PermissionField) {
                    $config = new PermissionFieldConfig(
                        $field,
                        $annotation->getOperations(),
                        $annotation->getMappingPermissions(),
                        $annotation->getEditable()
                    );

                    if (isset($configs[$field])) {
                        $configs[$field]->merge($config);
                    } else {
                        $configs[$field] = $config;
                    }
                }
            }
        }

        return $configs;
    }

    /**
     * Convert the permission fields.
     *
     * @param object[] $fieldAnnotations The annotations in fields of permission annotation
     *
     * @return PermissionFieldConfigInterface[]
     */
    private function convertPermissionFields(array $fieldAnnotations): array
    {
        $configs = [];

        foreach ($fieldAnnotations as $field => $annotation) {
            if ($annotation instanceof PermissionField) {
                $configs[] = $this->convertPermissionField($field, $annotation);
            }
        }

        return $configs;
    }

    /**
     * Convert the permission field.
     *
     * @param string          $field      The field name
     * @param PermissionField $annotation The permission field annotation
     *
     * @return PermissionFieldConfigInterface
     */
    private function convertPermissionField(string $field, PermissionField $annotation): PermissionFieldConfigInterface
    {
        return new PermissionFieldConfig(
            $field,
            $annotation->getOperations(),
            $annotation->getMappingPermissions(),
            $annotation->getEditable()
        );
    }
}
