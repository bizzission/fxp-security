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

use Sonatra\Component\Security\Exception\InvalidArgumentException;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractBaseIdentity
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * Constructor.
     *
     * @param string $identifier The identifier
     * @param string $type       The type
     *
     * @throws InvalidArgumentException When the identifier is empty
     * @throws InvalidArgumentException When the type is empty
     */
    public function __construct($type, $identifier)
    {
        if (empty($type)) {
            throw new InvalidArgumentException('The type cannot be empty');
        }

        if ('' === $identifier) {
            throw new InvalidArgumentException('The identifier cannot be empty');
        }

        $this->type = $type;
        $this->identifier = $identifier;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}
