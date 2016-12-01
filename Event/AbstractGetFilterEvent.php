<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Event;

use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Identity\SubjectIdentityInterface;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * The doctrine orm get filter event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AbstractGetFilterEvent extends Event
{
    /**
     * @var PermissionManagerInterface
     */
    protected $permissionManager;

    /**
     * @var SharingManagerInterface
     */
    protected $sharingManager;

    /**
     * @var SubjectIdentityInterface
     */
    protected $subject;

    /**
     * @var string
     */
    protected $sharingVisibility;

    /**
     * @var SecurityIdentityInterface[]
     */
    protected $sids;

    /**
     * @var string
     */
    protected $filter = '';

    /**
     * Constructor.
     *
     * @param PermissionManagerInterface  $permissionManager The permission manager
     * @param SharingManagerInterface     $sharingManager    The sharing manager
     * @param SubjectIdentityInterface    $subject           The subject
     * @param string                      $sharingVisibility The sharing visibility
     * @param SecurityIdentityInterface[] $sids              The security identities
     */
    public function __construct(PermissionManagerInterface $permissionManager,
                                SharingManagerInterface $sharingManager,
                                SubjectIdentityInterface $subject,
                                $sharingVisibility,
                                array $sids)
    {
        $this->permissionManager = $permissionManager;
        $this->sharingManager = $sharingManager;
        $this->subject = $subject;
        $this->sharingVisibility = $sharingVisibility;
        $this->sids = $sids;
    }

    /**
     * Get the Permission Manager.
     *
     * @return PermissionManagerInterface
     */
    public function getPermissionManager()
    {
        return $this->permissionManager;
    }

    /**
     * Get the sharing manager.
     *
     * @return SharingManagerInterface
     */
    public function getSharingManager()
    {
        return $this->sharingManager;
    }

    /**
     * Get the subject.
     *
     * @return SubjectIdentityInterface
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get the sharing visibility.
     *
     * @return string
     */
    public function getSharingVisibility()
    {
        return $this->sharingVisibility;
    }

    /**
     * Get the security identities.
     *
     * @return SecurityIdentityInterface[]
     */
    public function getSecurityIdentities()
    {
        return $this->sids;
    }

    /**
     * Set the filter.
     *
     * @param string $filter The filter
     *
     * @return self
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get the filter.
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }
}
