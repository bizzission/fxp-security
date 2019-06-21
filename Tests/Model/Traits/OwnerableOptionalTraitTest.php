<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Model\Traits;

use Fxp\Component\Security\Tests\Fixtures\Model\MockObjectOwnerableOptional;
use Fxp\Component\Security\Tests\Fixtures\Model\MockUserRoleable;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class OwnerableOptionalTraitTest extends TestCase
{
    public function testModel(): void
    {
        $user = new MockUserRoleable();
        $ownerable = new MockObjectOwnerableOptional('foo');

        static::assertNull($ownerable->getOwner());
        static::assertNull($ownerable->getOwnerId());

        $ownerable->setOwner($user);

        static::assertSame($user, $ownerable->getOwner());
        static::assertSame(50, $ownerable->getOwnerId());
    }
}
