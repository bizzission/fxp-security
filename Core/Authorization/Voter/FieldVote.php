<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Core\Authorization\Voter;

/**
 * Field vote.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FieldVote
{
    /**
     * @var object
     */
    private $domainObject;

    /**
     * @var string
     */
    private $field;

    /**
     * Constructor.
     *
     * @param object $domainObject The domain object
     * @param string $field        The field name
     */
    public function __construct($domainObject, $field)
    {
        $this->domainObject = $domainObject;
        $this->field = $field;
    }

    /**
     * Get the domain object.
     *
     * @return object
     */
    public function getDomainObject()
    {
        return $this->domainObject;
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
