<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Doctrine\ORM\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Fxp\Component\DoctrineExtensions\Filter\AbstractFilter;
use Fxp\Component\Security\Doctrine\ORM\Event\GetFilterEvent;
use Fxp\Component\Security\Identity\SubjectUtils;
use Fxp\Component\Security\Sharing\SharingManagerInterface;
use Fxp\Component\Security\SharingFilterEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Sharing filter.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SharingFilter extends AbstractFilter
{
    /**
     * @var null|SharingManagerInterface
     */
    protected $sm;

    /**
     * @var null|EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var null|string
     */
    protected $sharingClass;

    /**
     * Set the sharing manager.
     *
     * @param SharingManagerInterface $sharingManager The sharing manager
     *
     * @return $this
     */
    public function setSharingManager(SharingManagerInterface $sharingManager): SharingFilter
    {
        $this->sm = $sharingManager;

        return $this;
    }

    /**
     * Set the event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher
     *
     * @return $this
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher): SharingFilter
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Set the class name of the sharing model.
     *
     * @param string $class The class name of sharing model
     *
     * @return $this
     */
    public function setSharingClass($class): SharingFilter
    {
        $this->sharingClass = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function doAddFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        $name = SharingFilterEvents::getName(
            SharingFilterEvents::DOCTRINE_ORM_FILTER,
            $this->sm->getSharingVisibility(SubjectUtils::getSubjectIdentity($targetEntity->getName()))
        );
        $event = new GetFilterEvent($this, $this->getEntityManager(), $targetEntity, $targetTableAlias, $this->sharingClass);
        $this->dispatcher->dispatch($name, $event);

        return $event->getFilterConstraint();
    }

    /**
     * {@inheritdoc}
     *
     * @throws
     */
    protected function supports(ClassMetadata $targetEntity): bool
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
}
