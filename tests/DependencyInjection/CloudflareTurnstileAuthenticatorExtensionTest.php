<?php

declare(strict_types=1);

namespace Nuvola\CloudflareTurnstileAuthenticatorBundle\Tests\DependencyInjection;

use Nuvola\CloudflareTurnstileAuthenticatorBundle\DependencyInjection\CloudflareTurnstileAuthenticatorExtension;
use Nuvola\CloudflareTurnstileAuthenticatorBundle\Service\SiteService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CloudflareTurnstileAuthenticatorExtensionTest extends TestCase
{
    public function testLoadInternal(): void
    {
        $container = new ContainerBuilder();
        $extension = new CloudflareTurnstileAuthenticatorExtension();

        $mergedConfig = [
            'endpoint'   => 'http://turnstile.cloudflare.localhost/verify',
            'secret_key' => 'secret-key',
        ];

        $extension->loadInternal($mergedConfig, $container);

        $this->assertTrue($container->hasDefinition(SiteService::class));

        $definition = $container->getDefinition(SiteService::class);

        $this->assertSame('http://turnstile.cloudflare.localhost/verify', $definition->getArgument(1));
        $this->assertSame('secret-key', $definition->getArgument(2));
    }

    public function testGetAlias(): void
    {
        $extension = new CloudflareTurnstileAuthenticatorExtension();

        $this->assertSame('cf_turnstile_authenticator', $extension->getAlias());
    }
}
