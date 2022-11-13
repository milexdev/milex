<?php

namespace Milex\CoreBundle\Loader\EnvVars;

use Symfony\Component\HttpFoundation\ParameterBag;

class TwigEnvVars implements EnvVarsInterface
{
    public static function load(ParameterBag $config, ParameterBag $defaultConfig, ParameterBag $envVars): void
    {
        $tmpPath = $config->get('tmp_path');
        $envVars->set('MILEX_TWIG_CACHE_DIR', $tmpPath.'/twig');
    }
}
