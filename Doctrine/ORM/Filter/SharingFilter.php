<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Doctrine\ORM\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Sonatra\Component\DoctrineExtensions\Filter\AbstractFilter;
use Sonatra\Component\Security\Doctrine\ORM\Event\GetFilterEvent;
use Sonatra\Component\Security\Identity\SubjectUtils;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Sonatra\Component\Security\SharingFilterEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Sharing filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingFilter extends AbstractFilter
{
    /**
     * @var SharingManagerInterface|null
     */
    protected $sm;

    /**
     * @var EventDispatcherInterface|null
     */
    protected $dispatcher;

    /**
     * @var string|null
     */
    protected $sharingClass;

    /**
     * Set the sharing manager.
     *
     * @param SharingManagerInterface $sharingManager The sharing manager
     *
     * @return self
     */
    public function setSharingManager(SharingManagerInterface $sharingManager)
    {
        $this->sm = $sharingManager;

        return $this;
    }

    /**
     * Set the event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher
     *
     * @return self
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Set the class name of the sharing model.
     *
     * @param string $class The class name of sharing model
     *
     * @return self
     */
    public function setSharingClass($class)
    {
        $this->sharingClass = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(ClassMetadata $targetEntity)
    {
        $subject = SubjectUtils::getSubjectIdentity($targetEntity->getName());

        return $this->hasParameter('has_security_identities')
            && $this->hasParameter('map_security_identities')
            && $this->hasParameter('user_id')
            && $this->hasParameter('sharing_manager_enabled')
            && $this->getRealParameter('sharing_manager_enabled')
            && null !== $this->dispatcher
            && null !== $this->sm
            && null !== $this->sharingClass
            && $this->sm->hasSharingVisibility($subject);
    }

    /**
     * {@inheritdoc}
     */
    public function doAddFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        $name = SharingFilterEvents::getName(
            SharingFilterEvents::DOCTRINE_ORM_FILTER,
            $this->sm->getSharingVisibility(SubjectUtils::getSubjectIdentity($targetEntity->getName()))
        );
        $event = new GetFilterEvent(
            $this,
            $this->getEntityManager(),
            $targetEntity,
            $targetTableAlias,
            $this->sharingClass
        );
        $this->dispatcher->dispatch($name, $event);

        return $event->getFilterConstraint();
    }
}
