<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Event;

use Sonatra\Component\Security\Event\SetOrganizationalOptionalFilterTypeEvent;
use Sonatra\Component\Security\OrganizationalTypes;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SetOrganizationalOptionalFilterTypeEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $type = OrganizationalTypes::OPTIONAL_FILTER_ALL;

        $event = new SetOrganizationalOptionalFilterTypeEvent($type);

        $this->assertSame($type, $event->getOptionalFilterType());
    }
}
