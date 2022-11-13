<?php

namespace Milex\CoreBundle\DependencyInjection\EnvProcessor;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class MilexConstProcessor implements EnvVarProcessorInterface
{
    public function getEnv($prefix, $name, \Closure $getEnv)
    {
        return defined($name) ? constant($name) : null;
    }

    public static function getProvidedTypes()
    {
        return [
            'milexconst' => 'string',
        ];
    }
}
