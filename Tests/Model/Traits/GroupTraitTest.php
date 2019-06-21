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

use Fxp\Component\Security\Tests\Fixtures\Model\MockGroup;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class GroupTraitTest extends TestCase
{
    public function testGroupModel(): void
    {
        $group = new MockGroup('GROUP_TEST');

        static::assertSame('GROUP_TEST', $group->getName());

        $group->setName('GROUP_FOO');
        static::assertSame('GROUP_FOO', $group->getName());
    }
}
