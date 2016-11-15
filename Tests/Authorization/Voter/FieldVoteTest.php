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

        $this->assertSame($object, $fv->getDomainObject());
        $this->assertSame($field, $fv->getField());
    }
}
