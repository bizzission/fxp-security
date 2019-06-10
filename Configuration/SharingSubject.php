<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Configuration;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class SharingSubject extends AbstractConfiguration
{
    /**
     * @var null|string
     *
     * @see \Fxp\Component\Security\SharingVisibilities
     */
    protected $visibility;

    /**
     * @return null|string
     */
    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    /**
     * @param null|string $visibility
     */
    public function setVisibility(?string $visibility): void
    {
        $this->visibility = $visibility;
    }
}
