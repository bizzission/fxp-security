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

use Sonatra\Component\Security\Model\Traits\OwnerableOptionalInterface;
use Sonatra\Component\Security\Model\Traits\OwnerableOptionalTrait;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MockObjectOwnerableOptional extends MockObject implements OwnerableOptionalInterface
{
    use OwnerableOptionalTrait;
}
