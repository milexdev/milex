<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Auth\Provider\BasicAuth;

use Milex\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;

interface CredentialsInterface extends AuthCredentialsInterface
{
    public function getUsername(): ?string;

    public function getPassword(): ?string;
}
