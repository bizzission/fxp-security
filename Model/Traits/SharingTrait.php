<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Model\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait for sharing model.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
trait SharingTrait
{
    use PermissionsTrait;
    use RoleableTrait;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=244)
     */
    protected $subjectClass;

    /**
     * @var int|string|null
     *
     * @ORM\Column(type="string", length=36)
     */
    protected $subjectId;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=244)
     */
    protected $identityClass;

    /**
     * @var int|string|null
     *
     * @ORM\Column(type="string", length=244)
     */
    protected $identityName;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $enabled = true;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $startedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endedAt;

    /**
     * {@inheritdoc}
     */
    public function setSubjectClass($class)
    {
        $this->subjectClass = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectClass()
    {
        return $this->subjectClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubjectId($id)
    {
        $this->subjectId = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectId()
    {
        return $this->subjectId;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentityClass($class)
    {
        $this->identityClass = $class;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityClass()
    {
        return $this->identityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentityName($name)
    {
        $this->identityName = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityName()
    {
        return $this->identityName;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setStartedAt($date)
    {
        $this->startedAt = $date;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setEndedAt($date)
    {
        $this->endedAt = $date;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndedAt()
    {
        return $this->endedAt;
    }
}
