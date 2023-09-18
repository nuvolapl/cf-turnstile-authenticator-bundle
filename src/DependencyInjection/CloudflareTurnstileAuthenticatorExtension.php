<?php

declare(strict_types=1);

namespace Nuvola\CloudflareTurnstileAuthenticatorBundle\DependencyInjection;

use Nuvola\CloudflareTurnstileAuthenticatorBundle\Service\SiteService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class CloudflareTurnstileAuthenticatorExtension extends ConfigurableExtension
{
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yaml');

        $definition = $container->getDefinition(SiteService::class);
        $definition->replaceArgument(1, $mergedConfig['endpoint']);
        $definition->replaceArgument(2, $mergedConfig['secret_key']);
    }

    public function getAlias(): string
    {
        return 'cf_turnstile_authenticator';
    }
}
