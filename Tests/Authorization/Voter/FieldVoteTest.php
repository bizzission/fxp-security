<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Authorization\Voter;

use Sonatra\Component\Security\Authorization\Voter\FieldVote;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FieldVoteTest extends \PHPUnit_Framework_TestCase
{
    public function testFieldVote()
    {
        $object = $this->getMockBuilder(\stdClass::class)->getMock();
        $field = 'field';

        $fv = new FieldVote($object, $field);

        $this->assertSame($object, $fv->getSubject());
        $this->assertSame(get_class($object), $fv->getClass());
        $this->assertSame($field, $fv->getField());
    }

    public function testFieldVoteWithClassname()
    {
        $object = \stdClass::class;
        $field = 'field';

        $fv = new FieldVote($object, $field);

        $this->assertNull($fv->getSubject());
        $this->assertSame(\stdClass::class, $fv->getClass());
        $this->assertSame($field, $fv->getField());
    }

    /**
     * @expectedException \Sonatra\Component\Security\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "object|string", "integer" given
     */
    public function testFieldVoteWithInvalidSubject()
    {
        $object = 42;
        $field = 'field';

        new FieldVote($object, $field);
    }
}
