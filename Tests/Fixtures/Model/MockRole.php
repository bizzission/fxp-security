<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Fixtures\Model;

use Fxp\Component\Security\Model\RoleHierarchicalInterface;
use Fxp\Component\Security\Model\Traits\RoleHierarchicalTrait;
use Fxp\Component\Security\Model\Traits\RoleTrait;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MockRole implements RoleHierarchicalInterface
{
    use RoleTrait;
    use RoleHierarchicalTrait;

    /**
     * @var int|null
     */
    protected $id;

    /**
     * Constructor.
     *
     * @param string $name The unique name
     * @param int    $id   The id
     */
    public function __construct($name, $id = 23)
    {
        $this->name = $name;
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }
}
