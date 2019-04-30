<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOPgSql\Driver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Fxp\Component\Security\Doctrine\DoctrineUtils;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class DoctrineUtilsTest extends TestCase
{
    public function testGetIdentifier(): void
    {
        /** @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier')
        ;

        $targetClass->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn([
                'identifier',
                'next',
            ])
        ;

        $this->assertSame('identifier', DoctrineUtils::getIdentifier($targetClass));
        DoctrineUtils::clearCaches();
    }

    public function testGetIdentifierWithoutIdentifier(): void
    {
        /** @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier')
        ;

        $targetClass->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn([])
        ;

        $this->assertSame('id', DoctrineUtils::getIdentifier($targetClass));
        DoctrineUtils::clearCaches();
    }

    public function getFieldTypes()
    {
        return [
            [Type::GUID, '00000000-0000-0000-0000-000000000000'],
            [Type::STRING, ''],
            [Type::TEXT, ''],
            [Type::INTEGER, 0],
            [Type::SMALLINT, 0],
            [Type::BIGINT, 0],
            [Type::DECIMAL, 0],
            [Type::FLOAT, 0],
            [Type::BINARY, null],
            [Type::BLOB, null],
        ];
    }

    /**
     * @dataProvider getFieldTypes
     *
     * @param string     $type       The doctrine field type
     * @param int|string $validValue The valid value
     */
    public function testGetMockZeroId($type, $validValue): void
    {
        /** @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier')
        ;

        $targetClass->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn([
                'id',
            ])
        ;

        $targetClass->expects($this->once())
            ->method('getTypeOfField')
            ->with('id')
            ->willReturn($type)
        ;

        $this->assertSame($validValue, DoctrineUtils::getMockZeroId($targetClass));
        DoctrineUtils::clearCaches();
    }

    public function testCastIdentifier(): void
    {
        /** @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier')
        ;

        $targetClass->expects($this->atLeastOnce())
            ->method('getIdentifierFieldNames')
            ->willReturn([
                'id',
            ])
        ;

        $targetClass->expects($this->once())
            ->method('getTypeOfField')
            ->with('id')
            ->willReturn(Type::GUID)
        ;

        $dbPlatform = $this->getMockForAbstractClass(
            AbstractPlatform::class,
            [],
            '',
            true,
            true,
            true,
            [
                'getGuidTypeDeclarationSQL',
            ]
        );
        $dbPlatform->expects($this->once())
            ->method('getGuidTypeDeclarationSQL')
            ->with(['id'])
            ->willReturn('UUID')
        ;

        /** @var Connection|\PHPUnit_Framework_MockObject_MockObject $conn */
        $conn = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $conn->expects($this->atLeastOnce())
            ->method('getDatabasePlatform')
            ->willReturn($dbPlatform)
        ;
        $conn->expects($this->any())
            ->method('getDriver')
            ->willReturn($this->getMockBuilder(Driver::class)->disableOriginalConstructor()->getMock())
        ;

        $this->assertSame('::UUID', DoctrineUtils::castIdentifier($targetClass, $conn));
        DoctrineUtils::clearCaches();
    }

    public function testGetIdentifierTypeWithTypeString(): void
    {
        /** @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier')
        ;

        $targetClass->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn([
                'id',
            ])
        ;

        $type = Type::getType(Type::GUID);

        $targetClass->expects($this->once())
            ->method('getTypeOfField')
            ->with('id')
            ->willReturn($type)
        ;

        $this->assertEquals($type, DoctrineUtils::getIdentifierType($targetClass));
        DoctrineUtils::clearCaches();
    }

    public function testGetIdentifierTypeWithTypeInstance(): void
    {
        /** @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier')
        ;

        $targetClass->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn([
                'id',
            ])
        ;

        $type = Type::getType(Type::GUID);

        $targetClass->expects($this->once())
            ->method('getTypeOfField')
            ->with('id')
            ->willReturn($type)
        ;

        $this->assertSame($type, DoctrineUtils::getIdentifierType($targetClass));
        DoctrineUtils::clearCaches();
    }

    public function testGetIdentifierTypeWithInvalidType(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\RuntimeException::class);
        $this->expectExceptionMessage('The Doctrine DBAL type is not found for "TestIdentifier::id" identifier');

        /** @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier')
        ;

        $targetClass->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn([
                'id',
            ])
        ;

        $this->assertSame(42, DoctrineUtils::getIdentifierType($targetClass));
        DoctrineUtils::clearCaches();
    }
}
