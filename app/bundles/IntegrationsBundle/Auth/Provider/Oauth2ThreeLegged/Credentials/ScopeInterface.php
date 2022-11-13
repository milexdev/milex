<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Auth\Provider\Oauth2ThreeLegged\Credentials;

interface ScopeInterface
{
    public function getScope(): ?string;
}
