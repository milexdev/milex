<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if (!$app->getRequest()->isXmlHttpRequest() && false === $view['slots']->get('contentOnly', false)) :
    //load base template
    $view->extend('MilexInstallBundle:Install:base.html.php');
endif;
?>

<?php echo $view->render('MilexCoreBundle:Notification:flashes.html.php'); ?>

<?php $view['slots']->output('_content'); ?>
