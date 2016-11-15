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

/**
 * Object Filter Interface.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface ObjectFilterInterface
{
    /**
     * Get object filter unit of work.
     *
     * @return UnitOfWorkInterface
     */
    public function getUnitOfWork();

    /**
     * Begin the transaction.
     */
    public function beginTransaction();

    /**
     * Execute the transaction.
     */
    public function commit();

    /**
     * Filtering the object fields with null value for unauthorized access field.
     *
     * @param object $object The object instance
     *
     * @throws \InvalidArgumentException When $object is not a object instance
     */
    public function filter($object);

    /**
     * Restoring the object fields with old value for unauthorized access field.
     *
     * @param object $object The object instance
     *
     * @throws \InvalidArgumentException When $object is not a object instance
     */
    public function restore($object);
}
