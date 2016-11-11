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

use Sonatra\Component\Security\Acl\Model\PermissionContextInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * The acl manipulator event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AclManipulatorEvent extends Event
{
    /**
     * @var PermissionContextInterface
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param PermissionContextInterface $context
     */
    public function __construct(PermissionContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * Get acl permission context.
     *
     * @return PermissionContextInterface
     */
    public function getPermissionContext()
    {
        return $this->context;
    }
}
