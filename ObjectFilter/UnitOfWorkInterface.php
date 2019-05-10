<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\ObjectFilter;

/**
 * Object Filter Unit Of Work Interface.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface UnitOfWorkInterface
{
    /**
     * Gets the map of all identifiers of managed objects.
     *
     * @return array The managed object ids (spl_object_hash)
     */
    public function getObjectIdentifiers(): array;

    /**
     * Attaches an object from the object filter management.
     *
     * @param object $object The object to attach
     */
    public function attach($object): void;

    /**
     * Detaches an object from the object filter management.
     *
     * @param object $object The object to detach
     */
    public function detach($object): void;

    /**
     * Gets the changeset for an object.
     *
     * @param object $object
     *
     * @return array
     */
    public function getObjectChangeSet($object): array;

    /**
     * Clears the UnitOfWork.
     */
    public function flush(): void;
}
