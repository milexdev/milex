<?php

/*
 * @copyright   2018 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $view['translator']->trans('milex.config.tab.smsconfig'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
            <?php if (count($form['sms_transport']->vars['choices'])):?>
                <?php echo $view['form']->row($form['sms_transport']); ?>
            <?php else: ?>
                <?php echo $view['translator']->trans('milex.sms.config.smsconfig'); ?>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>
