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

use Fxp\Component\Security\Model\SharingInterface;
use Fxp\Component\Security\Model\Traits\SharingTrait;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MockSharing implements SharingInterface
{
    use SharingTrait;

    /**
     * @var null|int
     */
    protected $id;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }
}
