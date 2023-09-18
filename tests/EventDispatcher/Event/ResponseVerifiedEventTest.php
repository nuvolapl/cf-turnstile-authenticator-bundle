<?php

declare(strict_types=1);

namespace Nuvola\CloudflareTurnstileAuthenticatorBundle\Tests\EventDispatcher\Event;

use Nuvola\CloudflareTurnstileAuthenticatorBundle\EventDispatcher\Event\ResponseVerifiedEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class ResponseVerifiedEventTest extends TestCase
{
    public function testUserSet(): void
    {
        $user = $this->createMock(UserInterface::class);

        $event = new ResponseVerifiedEvent();
        $event->setUser($user);

        $this->assertTrue($event->isUserSet());
        $this->assertSame($user, $event->getUser());
    }

    public function testUserNotSet(): void
    {
        $event = new ResponseVerifiedEvent();

        $this->assertFalse($event->isUserSet());
        $this->assertNull($event->getUser());
    }
}
