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
    private $subject;

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
     * @param object|string $subject The subject instance or classname
     * @param string        $field   The field name
     */
    public function __construct($subject, $field)
    {
        if (is_string($subject)) {
            $this->class = $subject;
        } elseif (is_object($subject)) {
            $this->subject = $subject;
            $this->class = get_class($subject);
        } else {
            throw new UnexpectedTypeException($subject, 'object|string');
        }

        $this->field = $field;
    }

    /**
     * Get the subject.
     *
     * @return object|null
     */
    public function getSubject()
    {
        return $this->subject;
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
