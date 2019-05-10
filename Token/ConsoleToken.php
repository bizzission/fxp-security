<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * ConsoleToken represents an console token.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConsoleToken extends AbstractToken
{
    private $key;

    /**
     * Constructor.
     *
     * @param string   $key   The key shared with the authentication provider
     * @param string   $user  The user
     * @param string[] $roles An array of roles
     */
    public function __construct(string $key, string $user, array $roles = [])
    {
        parent::__construct($roles);

        $this->key = $key;
        $this->setUser($user);
        $this->setAuthenticated(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(): string
    {
        return '';
    }

    /**
     * Returns the key.
     *
     * @return string The Key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([$this->key, parent::serialize()]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        list($this->key, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
    }
}
