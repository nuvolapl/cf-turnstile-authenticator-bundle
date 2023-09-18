<?php

declare(strict_types=1);

namespace Nuvola\CloudflareTurnstileAuthenticatorBundle\Service;

interface SiteServiceInterface
{
    public function verify(string $response, string $idempotencyKey = null, string $remoteip = null): true;
}
