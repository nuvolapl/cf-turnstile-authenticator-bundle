<?php

declare(strict_types=1);

namespace Nuvola\CloudflareTurnstileAuthenticatorBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

final class NullUser implements UserInterface
{
    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void {}

    public function getUserIdentifier(): string
    {
        return '';
    }
}
