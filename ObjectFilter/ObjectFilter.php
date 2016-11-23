<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\ObjectFilter;

use Sonatra\Component\Security\Event\ObjectFieldViewGrantedEvent;
use Sonatra\Component\Security\Event\ObjectViewGrantedEvent;
use Sonatra\Component\Security\Event\PostCommitObjectFilterEvent;
use Sonatra\Component\Security\Event\PreCommitObjectFilterEvent;
use Sonatra\Component\Security\Event\RestoreViewGrantedEvent;
use Sonatra\Component\Security\Exception\UnexpectedTypeException;
use Sonatra\Component\Security\ObjectFilterEvents;
use Sonatra\Component\Security\Permission\FieldVote;
use Sonatra\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Object Filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectFilter implements ObjectFilterInterface
{
    /**
     * @var UnitOfWorkInterface
     */
    private $uow;

    /**
     * @var ObjectFilterExtensionInterface
     */
    private $ofe;

    /**
     * @var PermissionManagerInterface
     */
    private $pm;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $ac;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * If the action filtering/restoring is in a transaction, then the action
     * will be executing on the commit.
     *
     * @var bool
     */
    private $isTransactional = false;

    /**
     * The object list not analyzed (empty after commit).
     *
     * @var array
     */
    private $queue = array();

    /**
     * The object ids of object to filter (empty after commit).
     *
     * @var array
     */
    private $toFilter = array();

    /**
     * Constructor.
     *
     * @param ObjectFilterExtensionInterface $ofe        The object filter extension
     * @param PermissionManagerInterface     $pm         The permission manager
     * @param AuthorizationCheckerInterface  $ac         The authorization checker
     * @param EventDispatcherInterface       $dispatcher The event dispatcher
     * @param UnitOfWorkInterface            $uow        The unit of work
     */
    public function __construct(ObjectFilterExtensionInterface $ofe,
                                PermissionManagerInterface $pm,
                                AuthorizationCheckerInterface  $ac,
                                EventDispatcherInterface $dispatcher,
                                UnitOfWorkInterface $uow = null)
    {
        $this->uow = null !== $uow ? $uow : new UnitOfWork();
        $this->ofe = $ofe;
        $this->pm = $pm;
        $this->ac = $ac;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitOfWork()
    {
        return $this->uow;
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        $this->isTransactional = true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $event = new PreCommitObjectFilterEvent($this->queue);
        $this->dispatcher->dispatch(ObjectFilterEvents::PRE_COMMIT, $event);

        $this->pm->preloadPermissions(array_values($this->queue));

        foreach ($this->queue as $id => $object) {
            if (in_array($id, $this->toFilter)) {
                $this->doFilter($object);
            } else {
                $this->doRestore($object);
            }
        }

        $event = new PostCommitObjectFilterEvent($this->queue);
        $this->dispatcher->dispatch(ObjectFilterEvents::POST_COMMIT, $event);

        $this->queue = array();
        $this->isTransactional = false;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($object)
    {
        if (!is_object($object)) {
            throw new UnexpectedTypeException($object, 'object');
        }

        $id = spl_object_hash($object);

        $this->uow->attach($object);
        $this->queue[$id] = $object;
        $this->toFilter[] = $id;

        if (!$this->isTransactional) {
            $this->commit();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function restore($object)
    {
        if (!is_object($object)) {
            throw new UnexpectedTypeException($object, 'object');
        }

        $this->uow->attach($object);
        $this->queue[spl_object_hash($object)] = $object;

        if (!$this->isTransactional) {
            $this->commit();
        }
    }

    /**
     * Executes the filtering.
     *
     * @param object $object
     */
    protected function doFilter($object)
    {
        $clearAll = false;
        $id = spl_object_hash($object);
        array_splice($this->toFilter, array_search($id, $this->toFilter), 1);

        if (!$this->isViewGranted($object)) {
            $clearAll = true;
        }

        $ref = new \ReflectionClass($object);

        foreach ($ref->getProperties() as $property) {
            $property->setAccessible(true);
            $fieldVote = new FieldVote($object, $property->getName());
            $value = $property->getValue($object);

            if (null !== $value
                    && !$this->isIdentifier($fieldVote, $value)
                    && ($clearAll || !$this->isViewGranted($fieldVote))) {
                $value = $this->ofe->filterValue($value);
                $property->setValue($object, $value);
            }
        }
    }

    /**
     * Executes the restoring.
     *
     * @param object $object
     */
    protected function doRestore($object)
    {
        $changeSet = $this->uow->getObjectChangeSet($object);
        $ref = new \ReflectionClass($object);

        foreach ($changeSet as $field => $values) {
            $fv = new FieldVote($object, $field);

            if ($this->isRestoreViewGranted($fv, $values)) {
                $property = $ref->getProperty($field);
                $property->setAccessible(true);
                $property->setValue($object, $values['old']);
            }
        }
    }

    /**
     * Check if the field value must be restored.
     *
     * @param FieldVote $fieldVote The field vote
     * @param array     $values    The map of old and new values
     *
     * @return bool
     */
    protected function isRestoreViewGranted(FieldVote $fieldVote, array $values)
    {
        $event = new RestoreViewGrantedEvent($fieldVote, $values['old'], $values['new']);
        $this->dispatcher->dispatch(ObjectFilterEvents::RESTORE_VIEW_GRANTED, $event);

        if ($event->isSkipAuthorizationChecker()) {
            return !$event->isGranted();
        }

        return !$this->ac->isGranted('perm_view', $fieldVote)
            || !$this->ac->isGranted('perm_edit', $fieldVote);
    }

    /**
     * Check if the object or object field can be seen.
     *
     * @param object|FieldVote $object The object or field vote
     *
     * @return bool
     */
    protected function isViewGranted($object)
    {
        if ($object instanceof FieldVote) {
            $eventName = ObjectFilterEvents::OBJECT_FIELD_VIEW_GRANTED;
            $event = new ObjectFieldViewGrantedEvent($object);
        } else {
            $eventName = ObjectFilterEvents::OBJECT_VIEW_GRANTED;
            $event = new ObjectViewGrantedEvent($object);
        }

        $this->dispatcher->dispatch($eventName, $event);

        if ($event->isSkipAuthorizationChecker()) {
            return $event->isGranted();
        }

        return $this->ac->isGranted('perm_view', $object);
    }

    /**
     * Check if the field is an identifier.
     *
     * @param FieldVote $fieldVote The field vote
     * @param mixed     $value     The value
     *
     * @return bool
     */
    protected function isIdentifier(FieldVote $fieldVote, $value)
    {
        if ((string) $value === $fieldVote->getSubject()->getIdentifier()
            && in_array($fieldVote->getField(), array('id', 'objectIdentifier'))) {
            return true;
        }

        return false;
    }
}
