<?php

namespace Kunstmaan\MultiDomainBundle\Tests\Helper;

use Kunstmaan\MultiDomainBundle\Helper\DomainConfiguration;
use Kunstmaan\MultiDomainBundle\Helper\HostOverrideCleanupHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * @group legacy
 */
class HostOverrideCleanupHandlerTest extends TestCase
{
    public function testLogoutWithoutOverride()
    {
        if (!interface_exists(\Symfony\Component\Security\Http\Logout\LogoutHandlerInterface::class)) {
            $this->markTestSkipped('This test should only run on symfony 5.4 and lower');
        }

        $object = new HostOverrideCleanupHandler();
        $request = Request::create('/');
        $response = new Response();
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $object->logout($request, $response, $token);

        $this->assertFalse($request->hasSession());
    }

    /**
     * @covers \Kunstmaan\MultiDomainBundle\Helper\HostOverrideCleanupHandler::logout
     */
    public function testLogoutWithOverride()
    {
        if (!interface_exists(\Symfony\Component\Security\Http\Logout\LogoutHandlerInterface::class)) {
            $this->markTestSkipped('This test should only run on symfony 5.4 and lower');
        }

        $object = new HostOverrideCleanupHandler();
        $session = new Session(new MockArraySessionStorage());
        $session->set(DomainConfiguration::OVERRIDE_HOST, 'domain.tld');

        $request = Request::create('/');
        $request->setSession($session);
        $request->cookies->set($session->getName(), null);

        $response = new Response();
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $object->logout($request, $response, $token);

        $this->assertNull($session->get(DomainConfiguration::OVERRIDE_HOST));
    }
}
