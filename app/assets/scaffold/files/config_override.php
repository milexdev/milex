<?php

$container->setParameter('kernel.logs_dir', '%kernel.root_dir%/../../var/logs');
$container->setParameter('milex.cache_path', '%kernel.root_dir%/../../var/cache');
$container->setParameter('milex.log_path', '%kernel.root_dir%/../../var/logs');
$container->setParameter('milex.tmp_path', '%kernel.root_dir%/../../var/tmp');
$container->setParameter('milex.mailer_spool_path', '%kernel.root_dir%/../../var/tmp');
