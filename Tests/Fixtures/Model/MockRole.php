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

use Sonatra\Component\Security\Model\Role;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MockRole extends Role
{
    /**
     * Constructor.
     *
     * @param string $name The unique name
     * @param int    $id   The id
     */
    public function __construct($name, $id = 23)
    {
        parent::__construct($name);

        $this->id = $id;
    }
}
