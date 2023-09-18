<?php

declare(strict_types=1);

namespace Nuvola\CloudflareTurnstileAuthenticatorBundle;

use Nuvola\CloudflareTurnstileAuthenticatorBundle\DependencyInjection\CloudflareTurnstileAuthenticatorExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class CloudflareTurnstileAuthenticatorBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new CloudflareTurnstileAuthenticatorExtension();
    }
}
