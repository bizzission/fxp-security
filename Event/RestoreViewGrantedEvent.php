<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Event;

use Fxp\Component\Security\Permission\FieldVote;

/**
 * The object field view granted event.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class RestoreViewGrantedEvent extends AbstractViewGrantedEvent
{
    /**
     * @var FieldVote
     */
    protected $fieldVote;

    /**
     * @var mixed
     */
    protected $oldValue;

    /**
     * @var mixed
     */
    protected $newValue;

    /**
     * Constructor.
     *
     * @param FieldVote $fieldVote The permission field vote
     * @param mixed     $oldValue  The old value of field
     * @param mixed     $newValue  The new value of field
     */
    public function __construct(FieldVote $fieldVote, $oldValue, $newValue)
    {
        parent::__construct($this->validateFieldVoteSubject($fieldVote));

        $this->fieldVote = $fieldVote;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    /**
     * Get the permission field vote.
     *
     * @return FieldVote
     */
    public function getFieldVote(): FieldVote
    {
        return $this->fieldVote;
    }

    /**
     * Get the old value of field.
     *
     * @return mixed
     */
    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * Get the new value of field.
     *
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->newValue;
    }
}
