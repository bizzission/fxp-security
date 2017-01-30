<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\Type;
use Sonatra\Component\Security\Exception\RuntimeException;

/**
 * Utils for doctrine ORM.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class DoctrineUtils
{
    /**
     * @var array
     */
    private static $cacheIdentifiers = array();

    /**
     * @var array
     */
    private static $cacheZeroIds = array();

    /**
     * @var array
     */
    private static $cacheCastIdentifiers = array();

    /**
     * Clear the caches.
     */
    public static function clearCaches()
    {
        self::$cacheIdentifiers = array();
        self::$cacheZeroIds = array();
        self::$cacheCastIdentifiers = array();
    }

    /**
     * Get the identifier of entity.
     *
     * @param ClassMetadata $targetEntity The target entity
     *
     * @return string
     */
    public static function getIdentifier(ClassMetadata $targetEntity)
    {
        if (!isset(self::$cacheIdentifiers[$targetEntity->getName()])) {
            $identifier = $targetEntity->getIdentifierFieldNames();
            self::$cacheIdentifiers[$targetEntity->getName()] = 0 < count($identifier)
                ? $identifier[0]
                : 'id';
        }

        return self::$cacheIdentifiers[$targetEntity->getName()];
    }

    /**
     * Get the mock id for entity identifier.
     *
     * @param ClassMetadata $targetEntity The target entity
     *
     * @return int|string|null
     */
    public static function getMockZeroId(ClassMetadata $targetEntity)
    {
        if (!isset(self::$cacheZeroIds[$targetEntity->getName()])) {
            $type = self::getIdentifierType($targetEntity);
            self::$cacheZeroIds[$targetEntity->getName()] = self::findZeroIdValue($type);
        }

        return self::$cacheZeroIds[$targetEntity->getName()];
    }

    /**
     * Cast the identifier.
     *
     * @param ClassMetadata $targetEntity The target entity
     * @param Connection    $connection   The doctrine connection
     *
     * @return string
     */
    public static function castIdentifier(ClassMetadata $targetEntity, Connection $connection)
    {
        if (!isset(self::$cacheCastIdentifiers[$targetEntity->getName()])) {
            $cast = '';

            if ('postgresql' === $connection->getDatabasePlatform()->getName()) {
                $type = self::getIdentifierType($targetEntity);
                $cast = '::'.$type->getSQLDeclaration($targetEntity->getIdentifierFieldNames(),
                                                      $connection->getDatabasePlatform());
            }

            self::$cacheCastIdentifiers[$targetEntity->getName()] = $cast;
        }

        return self::$cacheCastIdentifiers[$targetEntity->getName()];
    }

    /**
     * Get the dbal identifier type.
     *
     * @param ClassMetadata $targetEntity The target entity
     *
     * @return Type
     *
     * @throws RuntimeException When the doctrine dbal type is not found
     */
    public static function getIdentifierType(ClassMetadata $targetEntity)
    {
        $identifier = self::getIdentifier($targetEntity);
        $type = $targetEntity->getTypeOfField($identifier);

        if ($type instanceof Type) {
            return $type;
        }

        if (is_string($type)) {
            return Type::getType($type);
        }

        $msg = 'The Doctrine DBAL type is not found for "%s::%s" identifier';
        throw new RuntimeException(sprintf($msg, $targetEntity->getName(), $identifier));
    }

    /**
     * Find the zero id by identifier type.
     *
     * @param Type $type The dbal identifier type
     *
     * @return string|int|null
     */
    private static function findZeroIdValue(Type $type)
    {
        if ($type instanceof GuidType) {
            $value = '00000000-0000-0000-0000-000000000000';
        } elseif (self::isNumberType($type)) {
            $value = 0;
        } elseif (self::isStringType($type)) {
            $value = '';
        } else {
            $value = null;
        }

        return $value;
    }

    /**
     * Check if the type is a number type.
     *
     * @param Type $type The dbal identifier type
     *
     * @return bool
     */
    private static function isNumberType(Type $type)
    {
        return $type instanceof IntegerType || $type instanceof SmallIntType || $type instanceof BigIntType || $type instanceof DecimalType || $type instanceof FloatType;
    }

    /**
     * Check if the type is a string type.
     *
     * @param Type $type The dbal identifier type
     *
     * @return bool
     */
    private static function isStringType(Type $type)
    {
        return $type instanceof StringType
            || $type instanceof TextType;
    }
}
