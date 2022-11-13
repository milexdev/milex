<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MilexCoreBundle:Default:content.html.php');
$view['slots']->set('headerTitle', $view['translator']->trans('milex.user.auth.expired.header'));
?>

<div class="row">
    <div class="col-xs-12 col-sm-8 col-md-6 inline-login">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>