<?php

declare(strict_types=1);

namespace Nuvola\CloudflareTurnstileAuthenticatorBundle\Tests\Security;

use Nuvola\CloudflareTurnstileAuthenticatorBundle\EventDispatcher\Event\ResponseVerifiedEvent;
use Nuvola\CloudflareTurnstileAuthenticatorBundle\Security\CloudflareTurnstileAuthenticator;
use Nuvola\CloudflareTurnstileAuthenticatorBundle\Security\User\NullUser;
use Nuvola\CloudflareTurnstileAuthenticatorBundle\Service\SiteServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class CloudflareTurnstileAuthenticatorTest extends TestCase
{
    private MockObject|SiteServiceInterface $siteServiceMock;

    private EventDispatcherInterface|MockObject $eventDispatcherMock;

    private CloudflareTurnstileAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->siteServiceMock     = $this->createMock(SiteServiceInterface::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

        $this->authenticator = new CloudflareTurnstileAuthenticator(
            $this->siteServiceMock,
            $this->eventDispatcherMock
        );
    }

    public function testSupportsWithHeader(): void
    {
        $request = new Request();
        $request->headers->set(
            'x-cf-turnstile-response',
            'valid-token'
        );

        $this->assertTrue($this->authenticator->supports($request));
    }

    public function testSupportsWithoutHeader(): void
    {
        $request = new Request();

        $this->assertFalse($this->authenticator->supports($request));
    }

    public function testAuthenticateWithValidToken(): void
    {
        $request = new Request();
        $request->headers->set(
            'x-cf-turnstile-response',
            'valid-token'
        );

        $this->siteServiceMock
            ->expects($this->once())
            ->method('verify')
            ->with('valid-token', $this->anything(), $this->anything())
            ->willReturn(true)
        ;

        $this->eventDispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with(new ResponseVerifiedEvent())
            ->willReturn(new ResponseVerifiedEvent())
        ;

        $passport = $this->authenticator->authenticate($request);

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);
        $this->assertInstanceOf(
            UserBadge::class,
            $passport->getBadge(UserBadge::class)
        );

        $user = $passport->getBadge(UserBadge::class)->getUserLoader()(
            'f57edf2a-9f12-52ec-8b95-e9b8dac04ac1'
        );

        $this->assertInstanceOf(NullUser::class, $user);
    }

    public function testAuthenticateWithValidTokenWithResolvedUser(): void
    {
        $request = new Request();
        $request->headers->set(
            'x-cf-turnstile-response',
            'valid-token'
        );

        $this->siteServiceMock
            ->expects($this->once())
            ->method('verify')
            ->with('valid-token', $this->anything(), $this->anything())
            ->willReturn(true)
        ;

        $event         = new ResponseVerifiedEvent();
        $eventWithUser = clone $event;
        $eventWithUser->setUser(new InMemoryUser('foo', 'bar'));

        $this->eventDispatcherMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willReturn($eventWithUser)
        ;

        $passport = $this->authenticator->authenticate($request);

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);
        $this->assertInstanceOf(
            UserBadge::class,
            $passport->getBadge(UserBadge::class)
        );

        $user = $passport->getBadge(UserBadge::class)->getUserLoader()(
            'f57edf2a-9f12-52ec-8b95-e9b8dac04ac1'
        );

        $this->assertInstanceOf(InMemoryUser::class, $user);
    }

    public function testAuthenticateWithInvalidToken(): void
    {
        $request = new Request();
        $request->headers->set(
            'x-cf-turnstile-response',
            'invalid-token'
        );

        $this->siteServiceMock
            ->expects($this->once())
            ->method('verify')
            ->with('invalid-token', $this->anything(), $this->anything())
            ->willThrowException(new \RuntimeException())
        ;

        $this->expectException(TokenNotFoundException::class);

        $this->authenticator->authenticate($request);
    }

    public function testAuthenticateWithMissingHeader(): void
    {
        $request = new Request();

        $this->expectException(CustomUserMessageAuthenticationException::class);

        $this->authenticator->authenticate($request);
    }
}
