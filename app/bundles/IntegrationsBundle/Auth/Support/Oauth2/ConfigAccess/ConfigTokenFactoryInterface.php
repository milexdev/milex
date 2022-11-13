<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Auth\Support\Oauth2\ConfigAccess;

use Milex\IntegrationsBundle\Auth\Provider\AuthConfigInterface;
use Milex\IntegrationsBundle\Auth\Support\Oauth2\Token\TokenFactoryInterface;

interface ConfigTokenFactoryInterface extends AuthConfigInterface
{
    public function getTokenFactory(): TokenFactoryInterface;
}
