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

use Sonatra\Component\Security\Event\Traits\SecurityIdentityEventTrait;

/**
 * The post security identity retrieval strategy event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PostSecurityIdentityEvent extends AbstractSecurityEvent
{
    use SecurityIdentityEventTrait;
}
