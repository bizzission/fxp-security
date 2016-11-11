<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Tests\Firewall;

use Sonatra\Component\Security\Firewall\HostRoleListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class HostRoleListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenStorage;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ListenerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $anonymousListener;

    /**
     * @var Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var HostRoleListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)->getMock();
        $this->config = array(
            'foo.bar.tld' => 'ROLE_HOST',
            '.*.baz.tld' => 'ROLE_HOST_BAZ',
            '.*.foo.*' => 'ROLE_HOST_FOO',
        );
        $this->anonymousListener = $this->getMockBuilder(ListenerInterface::class)->getMock();
        $this->request = $this->getMockBuilder(Request::class)->getMock();
        $this->event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $this->event->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->listener = new HostRoleListener($this->tokenStorage, $this->config, $this->anonymousListener);
    }

    public function testBasic()
    {
        $this->assertTrue($this->listener->isEnabled());
        $this->listener->setEnabled(false);
        $this->assertFalse($this->listener->isEnabled());
    }

    public function testHandleWithDisabledListener()
    {
        $this->listener->setEnabled(false);
        $this->listener->handle($this->event);
    }

    public function testHandleWithoutHostRole()
    {
        $this->request->expects($this->once())
            ->method('getHttpHost')
            ->willReturn('no.host-role.tld');

        $this->listener->handle($this->event);
    }

    public function testHandleWithoutToken()
    {
        $this->request->expects($this->once())
            ->method('getHttpHost')
            ->willReturn('foo.bar.tld');

        $this->tokenStorage->expects($this->at(0))
            ->method('getToken')
            ->willReturn(null);

        $this->anonymousListener->expects($this->once())
            ->method('handle');

        $this->tokenStorage->expects($this->at(1))
            ->method('getToken')
            ->willReturn(null);

        $this->listener->handle($this->event);
    }

    public function testHandleWithAlreadyRoleIncluded()
    {
        $token = new AnonymousToken('secret', 'user', array(
            'ROLE_HOST',
        ));

        $this->request->expects($this->once())
            ->method('getHttpHost')
            ->willReturn('foo.bar.tld');

        $this->tokenStorage->expects($this->at(0))
            ->method('getToken')
            ->willReturn(null);

        $this->anonymousListener->expects($this->once())
            ->method('handle');

        $this->tokenStorage->expects($this->at(1))
            ->method('getToken')
            ->willReturn($token);

        $this->tokenStorage->expects($this->once())
            ->method('setToken');

        $this->listener->handle($this->event);

        $this->assertCount(1, $token->getRoles());
    }

    public function getHosts()
    {
        return array(
            array('foo.bar.tld', 'ROLE_HOST'),
            array('foo.baz.tld', 'ROLE_HOST_BAZ'),
            array('a.foo.tld', 'ROLE_HOST_FOO'),
            array('b.foo.tld', 'ROLE_HOST_FOO'),
            array('a.foo.com', 'ROLE_HOST_FOO'),
            array('b.foo.com', 'ROLE_HOST_FOO'),
            array('a.foo.org', 'ROLE_HOST_FOO'),
            array('b.foo.org', 'ROLE_HOST_FOO'),
        );
    }

    /**
     * @dataProvider getHosts
     *
     * @param string $host      The host name
     * @param string $validRole The valid role
     */
    public function testHandle($host, $validRole)
    {
        $token = new AnonymousToken('secret', 'user', array(
            'ROLE_FOO',
        ));

        $this->request->expects($this->once())
            ->method('getHttpHost')
            ->willReturn($host);

        $this->tokenStorage->expects($this->at(0))
            ->method('getToken')
            ->willReturn(null);

        $this->anonymousListener->expects($this->once())
            ->method('handle');

        $this->tokenStorage->expects($this->at(1))
            ->method('getToken')
            ->willReturn($token);

        $this->tokenStorage->expects($this->once())
            ->method('setToken');

        $this->listener->handle($this->event);

        $this->assertCount(2, $token->getRoles());

        $roles = array();

        /* @var Role $role */
        foreach ($token->getRoles() as $role) {
            $roles[] = $role->getRole();
        }

        $this->assertTrue(in_array('ROLE_FOO', $roles));
        $this->assertTrue(in_array($validRole, $roles));
    }
}
