<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Permission;

use Fxp\Component\Security\Identity\SubjectIdentityInterface;
use Fxp\Component\Security\Permission\FieldVote;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class FieldVoteTest extends TestCase
{
    public function testFieldVote(): void
    {
        $object = new MockObject('foo');
        $field = 'name';

        $fv = new FieldVote($object, $field);

        $this->assertNotNull($fv->getSubject());
        $this->assertSame($object, $fv->getSubject()->getObject());
        $this->assertSame(\get_class($object), $fv->getSubject()->getType());
        $this->assertSame($field, $fv->getField());
    }

    public function testFieldVoteWithSubjectIdentity(): void
    {
        $object = $this->getMockBuilder(SubjectIdentityInterface::class)->getMock();
        $field = 'name';

        $fv = new FieldVote($object, $field);

        $this->assertSame($object, $fv->getSubject());
        $this->assertSame($field, $fv->getField());
    }

    public function testFieldVoteWithClassname(): void
    {
        $object = \stdClass::class;
        $field = 'field';

        $fv = new FieldVote($object, $field);

        $this->assertNull($fv->getSubject()->getObject());
        $this->assertSame(\stdClass::class, $fv->getSubject()->getType());
        $this->assertSame($field, $fv->getField());
    }

    public function testFieldVoteWithInvalidSubject(): void
    {
        $this->expectException(\Fxp\Component\Security\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Fxp\\Component\\Security\\Identity\\SubjectIdentityInterface|object|string", "integer" given');

        $object = 42;
        $field = 'field';

        new FieldVote($object, $field);
    }
}
