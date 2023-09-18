<?php

declare(strict_types=1);

namespace Nuvola\CloudflareTurnstileAuthenticatorBundle\Service;

use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class SiteService implements SiteServiceInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $endpoint,
        private string $secretKey,
    ) {}

    public function verify(string $response, string $idempotencyKey = null, string $remoteip = null): true
    {
        $data = [
            'secret'   => $this->secretKey,
            'response' => $response,
        ];

        if ($idempotencyKey) {
            $data['idempotency_key'] = $idempotencyKey;
        }

        if ($remoteip) {
            $data['remoteip'] = $remoteip;
        }

        $httpOptions = new HttpOptions();
        $httpOptions
            ->setBody($data)
        ;

        try {
            $data = $this->httpClient->request('POST', $this->endpoint, $httpOptions->toArray())->toArray();
        } catch (ExceptionInterface|TransportExceptionInterface $e) {
            $data = [
                'success'     => false,
                'error-codes' => [
                    $e->getMessage(),
                ],
            ];
        }

        if (isset($data['success']) && $data['success']) {
            return true;
        }

        // TODO: custom exception
        throw new \RuntimeException(
            implode($data['error-codes'] ?? ['Unexpected error.']),
            0,
            $e ?? null,
        );
    }
}
