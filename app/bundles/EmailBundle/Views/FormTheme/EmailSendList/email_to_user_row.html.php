<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<div class="row">
    <div class="col-md-6">
        <?php echo $view['form']->row($form['user_id']); ?>
    </div>
    <div class="col-md-6">
        <?php echo $view['form']->row($form['to_owner']); ?>
    </div>

</div>
    <?php echo $view['form']->row($form['to']); ?>
    <?php echo $view['form']->row($form['cc']); ?>
    <?php echo $view['form']->row($form['bcc']); ?>
    <?php echo $view['form']->rest($form); ?>
