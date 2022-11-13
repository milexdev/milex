<?php

declare(strict_types=1);

namespace Milex\InstallBundle\Exception;

class AlreadyInstalledException extends \Exception
{
    protected $message = 'Milex is already installed.';
}
