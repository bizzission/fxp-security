<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Doctrine\ORM\Event;

use Doctrine\ORM\Mapping\ClassMetadata;
use Sonatra\Component\Security\Event\AbstractGetFilterEvent;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\SubjectIdentityInterface;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;

/**
 * The doctrine orm get filter event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GetFilterEvent extends AbstractGetFilterEvent
{
    /**
     * @var ClassMetadata
     */
    protected $targetEntity;

    /**
     * @var string
     */
    protected $targetTableAlias;

    /**
     * Constructor.
     *
     * @param PermissionManagerInterface  $permissionManager The permission manager
     * @param SharingManagerInterface     $sharingManager    The sharing manager
     * @param SubjectIdentityInterface    $subject           The subject
     * @param string                      $sharingVisibility The sharing visibility
     * @param SecurityIdentityInterface[] $sids              The security identities
     * @param ClassMetaData               $targetEntity      The target entity
     * @param string                      $targetTableAlias  The target table alias
     */
    public function __construct(PermissionManagerInterface $permissionManager,
                                SharingManagerInterface $sharingManager,
                                SubjectIdentityInterface $subject,
                                $sharingVisibility,
                                array $sids,
                                ClassMetadata $targetEntity,
                                $targetTableAlias)
    {
        parent::__construct($permissionManager, $sharingManager, $subject, $sharingVisibility, $sids);

        $this->targetEntity = $targetEntity;
        $this->targetTableAlias = $targetTableAlias;
    }

    /**
     * Get the target entity.
     *
     * @return ClassMetadata
     */
    public function getTargetEntity()
    {
        return $this->targetEntity;
    }

    /**
     * Get the target table alias.
     *
     * @return string
     */
    public function getTargetTableAlias()
    {
        return $this->targetTableAlias;
    }
}
