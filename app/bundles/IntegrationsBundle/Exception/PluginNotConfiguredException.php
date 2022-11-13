<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Exception;

use Exception;

class PluginNotConfiguredException extends Exception
{
    protected $message = 'milex.integration.not_configured';
}
