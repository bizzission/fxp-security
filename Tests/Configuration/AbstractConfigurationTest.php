<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Configuration;

use Fxp\Component\Security\Configuration\AbstractConfiguration;
use Fxp\Component\Security\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class AbstractConfigurationTest extends TestCase
{
    /**
     * @throws
     */
    public function testConstructorWithInvalidConfiguration(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/Unknown key "foo" for annotation "@Mock_AbstractConfiguration_(\w+)"/');

        $this->getMockForAbstractClass(AbstractConfiguration::class, [[
            'foo' => 'bar',
        ]]);
    }
}
