<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Doctrine;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Sonatra\Component\Security\Doctrine\DoctrineUtils;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DoctrineUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testGetIdentifier()
    {
        /* @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier');

        $targetClass->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn(array(
                'identifier',
                'next',
            ));

        $this->assertSame('identifier', DoctrineUtils::getIdentifier($targetClass));
        DoctrineUtils::clearCaches();
    }

    public function testGetIdentifierWithoutIdentifier()
    {
        /* @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier');

        $targetClass->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn(array());

        $this->assertSame('id', DoctrineUtils::getIdentifier($targetClass));
        DoctrineUtils::clearCaches();
    }

    public function getFieldTypes()
    {
        return array(
            array(Type::GUID, '00000000-0000-0000-0000-000000000000'),
            array(Type::STRING, ''),
            array(Type::TEXT, ''),
            array(Type::INTEGER, 0),
            array(Type::SMALLINT, 0),
            array(Type::BIGINT, 0),
            array(Type::DECIMAL, 0),
            array(Type::FLOAT, 0),
            array(Type::BINARY, null),
            array(Type::BLOB, null),

        );
    }

    /**
     * @dataProvider getFieldTypes
     *
     * @param string     $type       The doctrine field type
     * @param string|int $validValue The valid value
     */
    public function testGetMockZeroId($type, $validValue)
    {
        /* @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier');

        $targetClass->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn(array(
                'id',
            ));

        $targetClass->expects($this->once())
            ->method('getTypeOfField')
            ->with('id')
            ->willReturn($type);

        $this->assertSame($validValue, DoctrineUtils::getMockZeroId($targetClass));
        DoctrineUtils::clearCaches();
    }

    public function testCastIdentifier()
    {
        /* @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier');

        $targetClass->expects($this->atLeastOnce())
            ->method('getIdentifierFieldNames')
            ->willReturn(array(
                'id',
            ));

        $targetClass->expects($this->once())
            ->method('getTypeOfField')
            ->with('id')
            ->willReturn(Type::GUID);

        $dbPlatform = $this->getMockForAbstractClass(
            AbstractPlatform::class,
            array(),
            '',
            true,
            true,
            true,
            array(
                'getGuidTypeDeclarationSQL',
            )
        );
        $dbPlatform->expects($this->any())
            ->method('getName')
            ->willReturn('postgresql');

        $dbPlatform->expects($this->once())
            ->method('getGuidTypeDeclarationSQL')
            ->with(array('id'))
            ->willReturn('UUID');

        /* @var Connection|\PHPUnit_Framework_MockObject_MockObject $conn */
        $conn = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $conn->expects($this->atLeastOnce())
            ->method('getDatabasePlatform')
            ->willReturn($dbPlatform);

        $this->assertSame('::UUID', DoctrineUtils::castIdentifier($targetClass, $conn));
        DoctrineUtils::clearCaches();
    }

    public function testGetIdentifierTypeWithTypeString()
    {
        /* @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier');

        $targetClass->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn(array(
                'id',
            ));

        $type = Type::getType(Type::GUID);

        $targetClass->expects($this->once())
            ->method('getTypeOfField')
            ->with('id')
            ->willReturn($type);

        $this->assertEquals($type, DoctrineUtils::getIdentifierType($targetClass));
        DoctrineUtils::clearCaches();
    }

    public function testGetIdentifierTypeWithTypeInstance()
    {
        /* @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier');

        $targetClass->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn(array(
                'id',
            ));

        $type = Type::getType(Type::GUID);

        $targetClass->expects($this->once())
            ->method('getTypeOfField')
            ->with('id')
            ->willReturn($type);

        $this->assertSame($type, DoctrineUtils::getIdentifierType($targetClass));
        DoctrineUtils::clearCaches();
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\RuntimeException
     * @expectedExceptionMessage The Doctrine DBAL type is not found for "TestIdentifier::id" identifier
     */
    public function testGetIdentifierTypeWithInvalidType()
    {
        /* @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $targetClass */
        $targetClass = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $targetClass->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('TestIdentifier');

        $targetClass->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn(array(
                'id',
            ));

        $this->assertSame(42, DoctrineUtils::getIdentifierType($targetClass));
        DoctrineUtils::clearCaches();
    }
}
