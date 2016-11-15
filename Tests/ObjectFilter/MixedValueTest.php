<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\ObjectFilter;

use Sonatra\Component\Security\ObjectFilter\MixedValue;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MixedValueTest extends \PHPUnit_Framework_TestCase
{
    public function getValues()
    {
        return array(
            array('string'),
            array(42),
            array(42.5),
            array(true),
            array(false),
            array(null),
            array(new \stdClass()),
        );
    }

    /**
     * @dataProvider getValues
     *
     * @param mixed $value The value
     */
    public function test($value)
    {
        $mv = new MixedValue();

        $this->assertTrue($mv->supports($value));
        $this->assertNull($mv->getValue($value));
    }
}
