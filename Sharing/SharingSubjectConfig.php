<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Sharing;

use Fxp\Component\Security\Exception\InvalidArgumentException;
use Fxp\Component\Security\SharingVisibilities;

/**
 * Sharing subject config.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
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
    public function __construct(string $type, string $visibility = SharingVisibilities::TYPE_NONE)
    {
        $this->type = $type;
        $this->visibility = $visibility;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(SharingSubjectConfigInterface $newConfig): void
    {
        if ($this->getType() !== $newConfig->getType()) {
            throw new InvalidArgumentException(sprintf(
                'The sharing subject config of "%s" can be merged only with the same type, given: "%s"',
                $this->getType(),
                $newConfig->getType()
            ));
        }

        if (SharingVisibilities::TYPE_NONE !== $newVisibility = $newConfig->getVisibility()) {
            $this->visibility = $newVisibility;
        }
    }
}
