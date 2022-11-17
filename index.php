<?php

/*
 * @copyright   2014 Milex, NP
 * @author      Milex
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
define('MILEX_ROOT_DIR', __DIR__);

// Fix for hosts that do not have date.timezone set, it will be reset based on users settings
date_default_timezone_set('UTC');

require_once 'autoload.php';

use Milex\CoreBundle\ErrorHandler\ErrorHandler;
use Milex\Middleware\MiddlewareBuilder;
use function Stack\run;

if (extension_loaded('apcu') && in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', '172.17.0.1'])) {
    @apcu_clear_cache();
}

ErrorHandler::register('dev');

run((new MiddlewareBuilder(new AppKernel('dev', true)))->resolve());
