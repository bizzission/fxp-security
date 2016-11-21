<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Authorization\Voter;

use Sonatra\Component\Security\Exception\UnexpectedTypeException;

/**
 * Field vote.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FieldVote
{
    /**
     * @var object|null
     */
    private $domainObject;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $field;

    /**
     * Constructor.
     *
     * @param object|string $domainObject The domain object or classname
     * @param string        $field        The field name
     */
    public function __construct($domainObject, $field)
    {
        if (is_string($domainObject)) {
            $this->class = $domainObject;
        } elseif (is_object($domainObject)) {
            $this->domainObject = $domainObject;
            $this->class = get_class($domainObject);
        } else {
            throw new UnexpectedTypeException($domainObject, 'object|string');
        }

        $this->field = $field;
    }

    /**
     * Get the domain object.
     *
     * @return object|null
     */
    public function getDomainObject()
    {
        return $this->domainObject;
    }

    /**
     * Get the classname.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Get the field name.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
}
