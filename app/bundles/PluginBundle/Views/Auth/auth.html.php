<?php

$view->extend('MilexCoreBundle:Default:slim.html.php');
$js = <<<JS
Milex.handleIntegrationCallback("$integration", "$csrfToken", "$code", "$callbackUrl", "$clientIdKey", "$clientSecretKey");
JS;
$view['assets']->addScriptDeclaration($js, 'bodyClose');
