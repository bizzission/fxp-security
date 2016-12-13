<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Fixtures\Model;

use Sonatra\Component\Security\Model\Traits\OwnerableInterface;
use Sonatra\Component\Security\Model\Traits\OwnerableTrait;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MockObjectOwnerable extends MockObject implements OwnerableInterface
{
    use OwnerableTrait;
}
