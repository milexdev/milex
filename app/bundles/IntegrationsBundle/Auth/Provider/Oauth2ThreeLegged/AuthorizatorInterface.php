<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Auth\Provider\Oauth2ThreeLegged;

use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated; Use Credentials\CredentialsInterface and \Milex\IntegrationsBundle\Integration\Interfaces\AuthenticationInterface instead
 */
interface AuthorizatorInterface
{
    public function isAuthorized(): bool;

    public function getAccessToken(): string;

    public function getAuthorizationUri(CredentialsInterface $credentials): string;

    public function handleCallback(Request $request): void;
}
