<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Firewall;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Inject the host role in security identity manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class HostRoleListener extends AbstractRoleListener
{
    /**
     * Handles anonymous authentication.
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     */
    public function handle(GetResponseEvent $event)
    {
        if ($this->isEnabled()) {
            $hostRole = $this->getHostRole($event);

            if (null !== $hostRole) {
                $this->sidManager->addSpecialRole($hostRole);
            }
        }
    }

    /**
     * Get the host role.
     *
     * @param GetResponseEvent $event The response event
     *
     * @return Role|null
     */
    protected function getHostRole(GetResponseEvent $event)
    {
        $hostRole = null;
        $hostname = $event->getRequest()->getHttpHost();

        foreach ($this->config as $host => $role) {
            if (preg_match('/'.$host.'/', $hostname)) {
                $hostRole = new Role($role);
                break;
            }
        }

        return $hostRole;
    }
}
