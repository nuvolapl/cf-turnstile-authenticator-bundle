<?php

declare(strict_types=1);

namespace Nuvola\CloudflareTurnstileAuthenticatorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public const DEFAULT_CF_TRUNSTILE_ENDPOINT = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('cf_turnstile_authenticator');
        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('endpoint')->cannotBeEmpty()->defaultValue(self::DEFAULT_CF_TRUNSTILE_ENDPOINT)->end()
            ->scalarNode('secret_key')->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
