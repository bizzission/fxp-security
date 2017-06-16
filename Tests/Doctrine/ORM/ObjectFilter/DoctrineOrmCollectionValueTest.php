<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Doctrine\ORM\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Sonatra\Component\Security\Doctrine\ORM\ObjectFilter\DoctrineOrmCollectionValue;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DoctrineOrmCollectionValueTest extends TestCase
{
    public function getValues()
    {
        return array(
            array($this->getMockBuilder(Collection::class)->getMock(), true),
            array($this->getMockBuilder(\stdClass::class)->getMock(), false),
            array('string', false),
            array(42, false),
        );
    }

    /**
     * @dataProvider getValues
     *
     * @param mixed $value  The value
     * @param bool  $result The expected result
     */
    public function testSupports($value, $result)
    {
        $collectionValue = new DoctrineOrmCollectionValue();

        $this->assertSame($result, $collectionValue->supports($value));
    }

    /**
     * @dataProvider getValues
     *
     * @param mixed $value The value
     */
    public function testGetValue($value)
    {
        $collectionValue = new DoctrineOrmCollectionValue();

        $newValue = $collectionValue->getValue($value);

        $this->assertNotSame($value, $newValue);
        $this->assertInstanceOf(ArrayCollection::class, $newValue);
        $this->assertCount(0, $newValue);
    }
}
