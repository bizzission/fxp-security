<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Validator\Constraints;

use Fxp\Component\Security\Validator\Constraints\Permission;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class PermissionTest extends TestCase
{
    public function testGetTargets(): void
    {
        $constraint = new Permission();

        static::assertEquals(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }
}
