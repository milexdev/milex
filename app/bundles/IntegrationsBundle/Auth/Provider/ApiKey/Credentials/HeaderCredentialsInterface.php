<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Auth\Provider\ApiKey\Credentials;

use Milex\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;

interface HeaderCredentialsInterface extends AuthCredentialsInterface
{
    public function getKeyName(): string;

    public function getApiKey(): ?string;
}
