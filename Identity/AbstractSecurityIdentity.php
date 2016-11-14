<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Identity;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractSecurityIdentity implements SecurityIdentityInterface
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * Constructor.
     *
     * @param string $identifier The identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(SecurityIdentityInterface $identity)
    {
        if (!$identity instanceof self || $this->getType() !== $identity->getType()) {
            return false;
        }

        return $this->getIdentifier() === $identity->getIdentifier();
    }

    /**
     * A textual representation of this security identity.
     *
     * This is not used for equality comparison, but only for debugging.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s(%s)', basename(get_class($this)), $this->getIdentifier());
    }
}
