<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Auth\Provider\Oauth2TwoLegged\Credentials;

use Milex\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;

interface ClientCredentialsGrantInterface extends AuthCredentialsInterface
{
    public function getAuthorizationUrl(): string;

    public function getClientId(): ?string;

    public function getClientSecret(): ?string;
}
