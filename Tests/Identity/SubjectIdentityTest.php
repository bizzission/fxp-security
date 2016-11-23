<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Identity;

use Sonatra\Component\Security\Identity\SubjectIdentity;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockObject;
use Sonatra\Component\Security\Tests\Fixtures\Model\MockSubjectObject;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SubjectIdentityTest extends \PHPUnit_Framework_TestCase
{
    public function testDebugInfo()
    {
        $object = new MockObject('foo');

        $si = new SubjectIdentity(get_class($object), (string) $object->getId(), $object);

        $this->assertSame('SubjectIdentity(Sonatra\Component\Security\Tests\Fixtures\Model\MockObject, 42)', (string) $si);
    }

    public function testTypeAndIdentifier()
    {
        $object = new MockObject('foo');

        $si = new SubjectIdentity(get_class($object), (string) $object->getId(), $object);

        $this->assertSame((string) $object->getId(), $si->getIdentifier());
        $this->assertSame(MockObject::class, $si->getType());
        $this->assertSame($object, $si->getObject());
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\InvalidArgumentException
     * @expectedExceptionMessage The type cannot be empty
     */
    public function testEmptyType()
    {
        new SubjectIdentity(null, '42');
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\InvalidArgumentException
     * @expectedExceptionMessage The identifier cannot be empty
     */
    public function testEmptyIdentifier()
    {
        new SubjectIdentity(MockObject::class, '');
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "object|null", "integer" given
     */
    public function testInvalidSubject()
    {
        new SubjectIdentity(MockObject::class, '42', 42);
    }

    public function getIdentities()
    {
        return array(
            array(new SubjectIdentity(MockObject::class, '42'), true),
            array(new SubjectIdentity(\stdClass::class, '42'), false),
            array(new SubjectIdentity(MockObject::class, '42', new MockObject('foo')), true),
            array(new SubjectIdentity(MockObject::class, '50', new MockObject('foo', 50)), false),
        );
    }

    /**
     * @dataProvider getIdentities
     *
     * @param mixed $value  The value
     * @param bool  $result The expected result
     */
    public function testEquals($value, $result)
    {
        $object = new MockObject('foo');
        $si = new SubjectIdentity(get_class($object), (string) $object->getId(), $object);

        $this->assertSame($result, $si->equals($value));
    }

    public function testFromClassname()
    {
        $si = SubjectIdentity::fromClassname(MockObject::class);

        $this->assertSame(MockObject::class, $si->getType());
        $this->assertSame('class', $si->getIdentifier());
        $this->assertNull($si->getObject());
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\InvalidSubjectIdentityException
     * @expectedExceptionMessage The class "FooBar" does not exist
     */
    public function testFromWithNonExistentClass()
    {
        SubjectIdentity::fromClassname('FooBar');
    }

    public function testFormObject()
    {
        $object = new MockObject('foo');

        $si = SubjectIdentity::fromObject($object);

        $this->assertSame(MockObject::class, $si->getType());
        $this->assertSame((string) $object->getId(), $si->getIdentifier());
        $this->assertSame($object, $si->getObject());
    }

    public function testFormObjectWithSubjectInstance()
    {
        $object = new MockSubjectObject('foo');

        $si = SubjectIdentity::fromObject($object);

        $this->assertSame(MockSubjectObject::class, $si->getType());
        $this->assertSame((string) $object->getSubjectIdentifier(), $si->getIdentifier());
        $this->assertSame($object, $si->getObject());
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\InvalidSubjectIdentityException
     * @expectedExceptionMessage Expected argument of type "object", "integer" given
     */
    public function testFormObjectWithNonObject()
    {
        /* @var object $object */
        $object = 42;

        SubjectIdentity::fromObject($object);
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\InvalidSubjectIdentityException
     * @expectedExceptionMessage The identifier cannot be empty
     */
    public function testFormObjectWithEmptyIdentifier()
    {
        $object = new MockObject('foo', null);

        SubjectIdentity::fromObject($object);
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\InvalidSubjectIdentityException
     * @expectedExceptionMessage The object must either implement the SubjectInterface, or have a method named "getId"
     */
    public function testFormObjectWithInvalidObject()
    {
        $object = new \stdClass();

        SubjectIdentity::fromObject($object);
    }
}
