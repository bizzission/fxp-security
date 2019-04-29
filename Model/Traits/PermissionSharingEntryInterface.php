<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Model\Traits;

use Doctrine\Common\Collections\Collection;
use Fxp\Component\Security\Model\SharingInterface;

/**
 * Interface of permission's sharing entries model.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface PermissionSharingEntryInterface
{
    /**
     * @return Collection|SharingInterface[]
     */
    public function getSharingEntries();
}
