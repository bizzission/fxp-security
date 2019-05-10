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

use Fxp\Component\Security\Model\PermissionInterface;
use Fxp\Component\Security\Model\Traits\PermissionSharingEntryInterface;
use Fxp\Component\Security\Model\Traits\PermissionSharingEntryTrait;
use Fxp\Component\Security\Model\Traits\PermissionTrait;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MockPermissionSharing implements PermissionInterface, PermissionSharingEntryInterface
{
    use PermissionTrait;
    use PermissionSharingEntryTrait;

    /**
     * @var null|int
     */
    protected $id;

    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}
