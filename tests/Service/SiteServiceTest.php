<?php

declare(strict_types=1);

namespace Nuvola\CloudflareTurnstileAuthenticatorBundle\Tests\Service;

use Nuvola\CloudflareTurnstileAuthenticatorBundle\Service\SiteService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SiteServiceTest extends TestCase
{
    private HttpClientInterface|MockObject $httpClientMock;

    private SiteService $siteService;

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);

        $this->siteService = new SiteService($this->httpClientMock, 'http://turnstile.cloudflare.localhost/verify', 'secret-key');
    }

    public function testVerifyWithSuccessfullResponse(): void
    {
        $response = [
            'success' => true,
        ];

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
            ->expects(self::once())
            ->method('toArray')
            ->willReturn($response)
        ;

        $this->httpClientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'http://turnstile.cloudflare.localhost/verify', [
                'body' => [
                    'secret'   => 'secret-key',
                    'response' => 'valid-token',
                ],
            ])
            ->willReturn($responseMock)
        ;

        $this->assertTrue($this->siteService->verify('valid-token'));
    }

    public function testVerifyWithUnsuccessfulResponse(): void
    {
        $response = [
            'success'     => false,
            'error-codes' => [
                'Invalid token.',
            ],
        ];

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
            ->expects(self::once())
            ->method('toArray')
            ->willReturn($response)
        ;

        $this->httpClientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'http://turnstile.cloudflare.localhost/verify', [
                'body' => [
                    'secret'   => 'secret-key',
                    'response' => 'invalid-token',
                ],
            ])
            ->willReturn($responseMock)
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid token.');

        $this->siteService->verify('invalid-token');
    }

    public function testVerifyWithException(): void
    {
        $exception = new \RuntimeException('Network error.');

        $this->httpClientMock
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'http://turnstile.cloudflare.localhost/verify', [
                'body' => [
                    'secret'   => 'secret-key',
                    'response' => 'valid-token',
                ],
            ])
            ->willThrowException($exception)
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Network error.');

        $this->siteService->verify('valid-token');
    }
}
