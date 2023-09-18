<?php

declare(strict_types=1);

namespace Nuvola\CloudflareTurnstileAuthenticatorBundle\EventDispatcher\Event;

use Symfony\Component\Security\Core\User\UserInterface;

final class ResponseVerifiedEvent
{
    private ?UserInterface $user = null;

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function isUserSet(): bool
    {
        return $this->user instanceof UserInterface;
    }

    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }
}
