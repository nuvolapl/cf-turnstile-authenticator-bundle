<?php

declare(strict_types=1);

namespace Nuvola\CloudflareTurnstileAuthenticatorBundle\DependencyInjection\Tests;

use Nuvola\CloudflareTurnstileAuthenticatorBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigurationTest extends TestCase
{
    public function testConfigTreeBuilder(): void
    {
        $configuration = new Configuration();
        $treeBuilder   = $configuration->getConfigTreeBuilder();

        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);

        $node = $configuration->getConfigTreeBuilder()->buildTree();

        self::assertSame('cf_turnstile_authenticator', $node->getName());
        self::assertSame(
            [
                'secret_key' => 'secret-key',
                'endpoint'   => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            ],
            $node->finalize(
                [
                    'secret_key' => 'secret-key',
                ],
            )
        );
    }
}
