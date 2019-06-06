<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Fixtures\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MockToken extends AbstractToken
{
    public function getCredentials()
    {
        return '';
    }
}
