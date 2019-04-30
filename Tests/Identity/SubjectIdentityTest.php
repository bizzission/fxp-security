<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Identity;

use Fxp\Component\Security\Identity\SubjectIdentity;
use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockSubjectObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SubjectIdentityTest extends TestCase
{
    public function testDebugInfo(): void
    {
        $object = new MockObject('foo');

        $si = new SubjectIdentity(\get_class($object), (string) $object->getId(), $object);

        $this->assertSame('SubjectIdentity(Fxp\Component\Security\Tests\Fixtures\Model\MockObject, 42)', (string) $si);
    }

    public function testTypeAndIdentifier(): void
    {
        $object = new MockObject('foo');

        $si = new SubjectIdentity(\get_class($object), (string) $object->getId(), $object);

        $this->assertSame((string) $object->getId(), $si->getIdentifier());
        $this->assertSame(MockObject::class, $si->getType());
        $this->assertSame($object, $si->getObject());
    }

    public function testEmptyType(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The type cannot be empty');

        new SubjectIdentity(null, '42');
    }

    public function testEmptyIdentifier(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The identifier cannot be empty');

        new SubjectIdentity(MockObject::class, '');
    }

    public function testInvalidSubject(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "object|null", "integer" given');

        new SubjectIdentity(MockObject::class, '42', 42);
    }

    public function getIdentities()
    {
        return [
            [new SubjectIdentity(MockObject::class, '42'), true],
            [new SubjectIdentity(\stdClass::class, '42'), false],
            [new SubjectIdentity(MockObject::class, '42', new MockObject('foo')), true],
            [new SubjectIdentity(MockObject::class, '50', new MockObject('foo', 50)), false],
        ];
    }

    /**
     * @dataProvider getIdentities
     *
     * @param mixed $value  The value
     * @param bool  $result The expected result
     */
    public function testEquals($value, $result): void
    {
        $object = new MockObject('foo');
        $si = new SubjectIdentity(\get_class($object), (string) $object->getId(), $object);

        $this->assertSame($result, $si->equals($value));
    }

    public function testFromClassname(): void
    {
        $si = SubjectIdentity::fromClassname(MockObject::class);

        $this->assertSame(MockObject::class, $si->getType());
        $this->assertSame('class', $si->getIdentifier());
        $this->assertNull($si->getObject());
    }

    public function testFromClassnameWithNonExistentClass(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\InvalidSubjectIdentityException::class);
        $this->expectExceptionMessage('The class "FooBar" does not exist');

        SubjectIdentity::fromClassname('FooBar');
    }

    public function testFromObject(): void
    {
        $object = new MockObject('foo');

        $si = SubjectIdentity::fromObject($object);

        $this->assertSame(MockObject::class, $si->getType());
        $this->assertSame((string) $object->getId(), $si->getIdentifier());
        $this->assertSame($object, $si->getObject());
    }

    public function testFromObjectWithSubjectInstance(): void
    {
        $object = new MockSubjectObject('foo');

        $si = SubjectIdentity::fromObject($object);

        $this->assertSame(MockSubjectObject::class, $si->getType());
        $this->assertSame((string) $object->getSubjectIdentifier(), $si->getIdentifier());
        $this->assertSame($object, $si->getObject());
    }

    public function testFromObjectWithSubjectIdentityInstance(): void
    {
        $object = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();

        $si = SubjectIdentity::fromObject($object);

        $this->assertSame($object, $si);
    }

    public function testFromObjectWithNonObject(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\InvalidSubjectIdentityException::class);
        $this->expectExceptionMessage('Expected argument of type "object", "integer" given');

        /** @var object $object */
        $object = 42;

        SubjectIdentity::fromObject($object);
    }

    public function testFromObjectWithEmptyIdentifier(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\InvalidSubjectIdentityException::class);
        $this->expectExceptionMessage('The identifier cannot be empty');

        $object = new MockObject('foo', null);

        SubjectIdentity::fromObject($object);
    }

    public function testFromObjectWithInvalidObject(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\InvalidSubjectIdentityException::class);
        $this->expectExceptionMessage('The object must either implement the SubjectInterface, or have a method named "getId"');

        $object = new \stdClass();

        SubjectIdentity::fromObject($object);
    }
}
