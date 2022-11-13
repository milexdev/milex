<?php

ob_start();
echo $view->render('MilexFocusBundle:Builder\Bar:parent.less.php');
echo $view->render('MilexFocusBundle:Builder\Modal:parent.less.php');
echo $view->render('MilexFocusBundle:Builder\Notification:parent.less.php');
echo $view->render('MilexFocusBundle:Builder\Page:parent.less.php');

$less = ob_get_clean();

require_once __DIR__.'/../../Include/lessc.inc.php';
$compiler = new \lessc();
$css      = $compiler->compile($less);

if (empty($preview) && 'dev' != $app->getEnvironment()) {
    $css = \Minify_CSS::minify($css);
}

echo $css;
