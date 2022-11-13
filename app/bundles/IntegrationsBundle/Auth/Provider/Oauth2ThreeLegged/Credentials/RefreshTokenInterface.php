<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Auth\Provider\Oauth2ThreeLegged\Credentials;

use Milex\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;

interface RefreshTokenInterface extends AuthCredentialsInterface
{
    public function getRefreshToken(): ?string;
}
