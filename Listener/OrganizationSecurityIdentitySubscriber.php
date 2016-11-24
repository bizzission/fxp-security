<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Listener;

use Sonatra\Component\Security\Identity\CacheSecurityIdentityListenerInterface;
use Sonatra\Component\Security\Organizational\OrganizationalContextInterface;
use Sonatra\Component\Security\Event\AddSecurityIdentityEvent;
use Sonatra\Component\Security\Identity\OrganizationSecurityIdentity;
use Sonatra\Component\Security\SecurityIdentityEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Subscriber for add organization security identity from token.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrganizationSecurityIdentitySubscriber implements EventSubscriberInterface, CacheSecurityIdentityListenerInterface
{
    /**
     * @var RoleHierarchyInterface
     */
    private $roleHierarchy;

    /**
     * @var OrganizationalContextInterface
     */
    private $context;

    /**
     * Constructor.
     *
     * @param RoleHierarchyInterface         $roleHierarchy The role hierarchy
     * @param OrganizationalContextInterface $context       The organizational context
     */
    public function __construct(RoleHierarchyInterface $roleHierarchy,
                                OrganizationalContextInterface $context)
    {
        $this->roleHierarchy = $roleHierarchy;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            SecurityIdentityEvents::RETRIEVAL_ADD => array('addOrganizationSecurityIdentities', 0),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheId()
    {
        $org = $this->context->getCurrentOrganization();

        return null !== $org
            ? 'org'.$org->getId()
            : '';
    }

    /**
     * Add organization security identities.
     *
     * @param AddSecurityIdentityEvent $event The event
     */
    public function addOrganizationSecurityIdentities(AddSecurityIdentityEvent $event)
    {
        try {
            $sids = $event->getSecurityIdentities();
            $sids = array_merge($sids, OrganizationSecurityIdentity::fromToken($event->getToken(),
                $this->context, $this->roleHierarchy));
            $event->setSecurityIdentities($sids);
        } catch (\InvalidArgumentException $e) {
            // ignore
        }
    }
}
