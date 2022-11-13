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
$view['slots']->set('milexContent', 'user');
$view['slots']->set('headerTitle', $view['translator']->trans('milex.user.user.header.contact', ['%name%' => $user->getName()]));
?>

<div class="panel">
    <div class="panel-body pa-md">
        <?php echo $view['form']->form($form); ?>
    </div>
</div>
