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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait of permission's sharing entries model.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
trait PermissionSharingEntryTrait
{
    /**
     * @ORM\ManyToMany(targetEntity="Fxp\Component\Security\Model\SharingInterface", mappedBy="permissions")
     */
    protected $sharingEntries;

    /**
     * {@inheritdoc}
     */
    public function getSharingEntries()
    {
        return $this->sharingEntries ?: $this->sharingEntries = new ArrayCollection();
    }
}
