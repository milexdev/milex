<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Auth\Provider\Oauth2TwoLegged\Credentials;

interface ScopeInterface
{
    public function getScope(): ?string;
}
