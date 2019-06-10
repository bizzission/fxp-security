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

use Doctrine\Common\Annotations\Reader;
use Fxp\Component\Security\Annotation\ClassFinder;
use Fxp\Component\Security\Configuration\Permission;
use Fxp\Component\Security\Configuration\PermissionField;
use Fxp\Component\Security\Permission\PermissionConfig;
use Fxp\Component\Security\Permission\PermissionConfigInterface;
use Fxp\Component\Security\Permission\PermissionFieldConfig;
use Fxp\Component\Security\Permission\PermissionFieldConfigInterface;

/**
 * The permission annotation loader.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class AnnotationLoader implements LoaderInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var ClassFinder
     */
    private $classFinder;

    /**
     * Constructor.
     *
     * @param Reader      $reader      The annotation reader
     * @param ClassFinder $classFinder The class finder
     */
    public function __construct(Reader $reader, ClassFinder $classFinder)
    {
        $this->reader = $reader;
        $this->classFinder = $classFinder;
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfigurations(): array
    {
        $configs = [];

        foreach ($this->classFinder->findClasses() as $class) {
            try {
                $refClass = new \ReflectionClass($class);
                $configs = $this->getConfigurations($refClass, $configs);

                if (!empty($fieldConfigurations = $this->getFieldConfigurations($refClass))) {
                    if (isset($configs[$class])) {
                        $configs[$class]->merge(new PermissionConfig($class, [], [], $fieldConfigurations));
                    } else {
                        $configs[$class] = new PermissionConfig($class, [], [], $fieldConfigurations);
                    }
                }
            } catch (\ReflectionException $e) {
                // skip
            }
        }

        return array_values($configs);
    }

    /**
     * Get the permission configurations.
     *
     * @param \ReflectionClass            $refClass The reflection class
     * @param PermissionConfigInterface[] $configs  The map of permission config
     *
     * @return PermissionConfigInterface[]
     */
    private function getConfigurations(\ReflectionClass $refClass, array $configs): array
    {
        $class = $refClass->name;
        $classAnnotations = $this->reader->getClassAnnotations($refClass);

        foreach ($classAnnotations as $annotation) {
            if ($annotation instanceof Permission) {
                $config = new PermissionConfig(
                    $class,
                    $annotation->getOperations(),
                    $annotation->getMappingPermissions(),
                    $this->convertPermissionFields($annotation->getFields()),
                    $annotation->getMaster(),
                    $annotation->getMasterFieldMappingPermissions(),
                    $annotation->getBuildFields(),
                    $annotation->getBuildDefaultFields()
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
            $field = $refProperty->getName();

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
