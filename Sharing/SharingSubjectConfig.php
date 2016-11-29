<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Sharing;

use Sonatra\Component\Security\SharingVisibilities;

/**
 * Sharing subject config.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingSubjectConfig implements SharingSubjectConfigInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $visibility;

    /**
     * Constructor.
     *
     * @param string $type       The type, typically, this is the PHP class name
     * @param string $visibility The sharing visibility
     */
    public function __construct($type, $visibility = SharingVisibilities::TYPE_NONE)
    {
        $this->type = $type;
        $this->visibility = $visibility;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility()
    {
        return $this->visibility;
    }
}
