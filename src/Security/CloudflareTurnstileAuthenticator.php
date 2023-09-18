<?php

declare(strict_types=1);

namespace Nuvola\CloudflareTurnstileAuthenticatorBundle\Security;

use Nuvola\CloudflareTurnstileAuthenticatorBundle\EventDispatcher\Event\ResponseVerifiedEvent;
use Nuvola\CloudflareTurnstileAuthenticatorBundle\Security\User\NullUser;
use Nuvola\CloudflareTurnstileAuthenticatorBundle\Service\SiteServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Uid\Uuid;

final class CloudflareTurnstileAuthenticator extends AbstractAuthenticator
{
    private const HEADER_NAME = 'x-cf-turnstile-response';

    public function __construct(
        private readonly SiteServiceInterface $siteService,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->headers->has(self::HEADER_NAME);
    }

    public function authenticate(Request $request): Passport
    {
        $response = $request->headers->get(self::HEADER_NAME);

        if (null === $response) {
            throw new CustomUserMessageAuthenticationException(
                sprintf('Header "%s" cannot be empty.', self::HEADER_NAME)
            );
        }

        $idempotencyKey = Uuid::v5(Uuid::fromString(Uuid::NAMESPACE_DNS), hash('ripemd160', $response))->toRfc4122();

        try {
            $this->siteService->verify($response, $idempotencyKey, $request->getClientIp());
        } catch (\RuntimeException $e) {
            throw new TokenNotFoundException('Invalid token.', 0, $e);
        }

        $event = $this->eventDispatcher?->dispatch(new ResponseVerifiedEvent());

        return new SelfValidatingPassport(
            new UserBadge(
                $idempotencyKey,
                function (string $identifier) use ($event) {
                    if ($event && $event->isUserSet()) {
                        return $event->getUser();
                    }

                    return new NullUser();
                }
            ),
        );
    }

    // f57edf2a-9f12-52ec-8b95-e9b8dac04ac1
    // f57edf2a-9f12-52ec-8b95-e9b8dac04ac1
    // f57edf2a-9f12-52ec-8b95-e9b8dac04ac1
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}
