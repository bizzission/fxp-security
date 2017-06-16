<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Validator\Constraints;

use PHPUnit\Framework\TestCase;
use Sonatra\Component\Security\Validator\Constraints\Permission;
use Symfony\Component\Validator\Constraint;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PermissionTest extends TestCase
{
    public function testGetTargets()
    {
        $constraint = new Permission();

        $this->assertEquals(Constraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }
}
