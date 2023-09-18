# Nuvola Cloudflare Turnstile Authenticator Bundle 
[![.github/workflows/main.yaml](https://github.com/nuvolapl/cf-turnstile-authenticator-bundle/actions/workflows/main.yaml/badge.svg)](https://github.com/nuvolapl/cf-turnstile-authenticator-bundle/actions/workflows/main.yaml)

This bundle provides authentication based on the response from [Cloudflare Turnstile](https://www.cloudflare.com/products/turnstile/).

## Configuration

### To install the bundle, follow these steps:

- The following parameters are required for bundle configuration in the `./config/packages/cf_turnstile_authenticator.yaml` file:

```yaml
cf_turnstile_authenticator:
    secret_key: '%env(string:CF_TURNSTILE_AUTHENTICATOR_SECRET_KEY)%'
```

- add the `CF_TURNSTILE_AUTHENTICATOR_SECRET_KEY` environment variable to the `.env` file with a [dummy secret key](https://developers.cloudflare.com/turnstile/reference/testing/#dummy-sitekeys-and-secret-keys/)
- add the `CF_TURNSTILE_AUTHENTICATOR_SECRET_KEY` environment variable to the `.env.local` file with the secret key from [Cloudflare Turnstile](https://www.cloudflare.com/products/turnstile/)

## Installation

### To install the bundle, follow these steps:

- Run the following command to install the bundle:

```shell
composer require nuvola/cloudflare-turnstile-authenticator-bundle
```
- add the bundle to the `./config/bundles.php` file:

```php
<?php
// ...
    Nuvola\CloudflareTurnstileAuthenticatorBundle\CloudflareTurnstileAuthenticatorBundle::class => ['all' => true],
// ...
```

- to use the bundle, add the following code to the `./config/packages/security.yaml` file:

```yaml
security:
# ...
    firewalls:
# ...
        # adjust the name and pattern to your application!
        public:
            pattern: ^/api/public/
            stateless: true
            custom_authenticators:
              - Nuvola\CloudflareTurnstileAuthenticatorBundle\Security\CloudflareTurnstileAuthenticator
# ...
    access_control:
      - { path: ^/api/public/, roles: IS_AUTHENTICATED_FULLY }
# ...
```

After adding this configuration, only authenticated by [response token](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/) from the [Cloudflare Turnstile](https://developers.cloudflare.com/turnstile/get-started/server-side-validation/) will be passed.

## Usage
```shell
curl -H "x-cf-turnstile-response: $RESPONSE" https://api.nuvola.pl/api/public/users/7ff847d9-a2e0-4f93-9c00-b59ecd51a766
```
- $RESPONSE is a variable that stores [the token retrieved](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/) in the web browser
