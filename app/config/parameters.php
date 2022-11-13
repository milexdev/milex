<?php

$root = $container->getParameter('kernel.root_dir');
include __DIR__.'/paths_helper.php';

//load default parameters from bundle files
$core    = $container->getParameter('milex.bundles');
$plugins = $container->getParameter('milex.plugin.bundles');

$bundles = array_merge($core, $plugins);
unset($core, $plugins);

$milexParams = [];

foreach ($bundles as $bundle) {
    if (!empty($bundle['config']['parameters'])) {
        $milexParams = array_merge($milexParams, $bundle['config']['parameters']);
    }
}

// Set the parameters in the container with env processors
foreach ($milexParams as $k => $v) {
    switch (true) {
        case is_bool($v):
            $type = 'bool:';
            break;
        case is_int($v):
            // some configuration entries require processor to return explicit int, instead of string|int type,
            // which is returned by \Milex\CoreBundle\DependencyInjection\EnvProcessor\IntNullableProcessor
            if ('rememberme_lifetime' === $k) {
                $type = 'int:';
                break;
            }

            $type = 'intNullable:';
            break;
        case is_array($v):
            $type = 'json:';
            break;
        case is_float($v):
            $type = 'float:';
            break;
        default:
            $type = 'nullable:';
    }

    // Add to the container with the applicable processor
    $container->setParameter("milex.{$k}", sprintf('%%env(%sresolve:MILEX_%s)%%', $type, mb_strtoupper($k)));
}

// Set the router URI for CLI
$container->setParameter('router.request_context.host', '%env(MILEX_REQUEST_CONTEXT_HOST)%');
$container->setParameter('router.request_context.scheme', '%env(MILEX_REQUEST_CONTEXT_SCHEME)%');
$container->setParameter('router.request_context.base_url', '%env(MILEX_REQUEST_CONTEXT_BASE_URL)%');
$container->setParameter('request_listener.http_port', '%env(MILEX_REQUEST_CONTEXT_HTTP_PORT)%');
$container->setParameter('request_listener.https_port', '%env(MILEX_REQUEST_CONTEXT_HTTPS_PORT)%');

unset($milexParams, $replaceRootPlaceholder, $bundles);
