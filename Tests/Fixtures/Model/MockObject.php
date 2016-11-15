<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Fixtures\Model;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MockObject
{
    /**
     * @var string
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param string $name The name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Get the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name.
     *
     * @param string $name The name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
