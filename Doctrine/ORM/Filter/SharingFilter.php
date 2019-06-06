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
use Fxp\Component\Security\Doctrine\DoctrineSharingVisibilities;
use Fxp\Component\Security\Doctrine\ORM\Event\AbstractGetFilterEvent;
use Fxp\Component\Security\Doctrine\ORM\Event\GetNoneFilterEvent;
use Fxp\Component\Security\Identity\SubjectUtils;
use Fxp\Component\Security\Sharing\SharingManagerInterface;
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
     * @return static
     */
    public function setSharingManager(SharingManagerInterface $sharingManager): self
    {
        $this->sm = $sharingManager;

        return $this;
    }

    /**
     * Set the event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher
     *
     * @return static
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher): self
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Set the class name of the sharing model.
     *
     * @param string $class The class name of sharing model
     *
     * @return static
     */
    public function setSharingClass($class): self
    {
        $this->sharingClass = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function doAddFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        $visibility = $this->sm->getSharingVisibility(SubjectUtils::getSubjectIdentity($targetEntity->getName()));
        $eventClass = DoctrineSharingVisibilities::$classMap[$visibility] ?? GetNoneFilterEvent::class;
        /** @var AbstractGetFilterEvent $event */
        $event = new $eventClass($this, $this->getEntityManager(), $targetEntity, $targetTableAlias, $this->sharingClass);
        $this->dispatcher->dispatch($event);

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
