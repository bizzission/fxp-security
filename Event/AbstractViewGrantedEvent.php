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

use Symfony\Component\EventDispatcher\Event;

/**
 * The abstract view granted event.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractViewGrantedEvent extends Event
{
    /**
     * @var object
     */
    protected $object;

    /**
     * @var bool
     */
    protected $isGranted = true;

    /**
     * @var bool
     */
    protected $skip = false;

    /**
     * Constructor.
     *
     * @param object $object The object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * Get the object.
     *
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Defined if the user has the view access of this object.
     *
     * @param bool $isGranted The granted value
     *
     * @return self
     */
    public function setGranted($isGranted)
    {
        $this->isGranted = (bool) $isGranted;
        $this->skipAuthorizationChecker(true);

        return $this;
    }

    /**
     * Check if the user has the view access of this object.
     *
     * @return bool
     */
    public function isGranted()
    {
        return $this->isGranted;
    }

    /**
     * Skip the permission authorization checker or not.
     *
     * @param bool $skip The value
     *
     * @return self
     */
    public function skipAuthorizationChecker($skip)
    {
        $this->skip = (bool) $skip;

        return $this;
    }

    /**
     * Check if the permission authorization checker must be skipped or not.
     *
     * @return bool
     */
    public function isSkipAuthorizationChecker()
    {
        return $this->skip;
    }
}
