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
    <div class="col-md-5 pull-right">
        <?php echo $view['form']->start($form); ?>
            <div class="input-group">
                <?php echo $view['form']->widget($form['file']); ?>
                <span class="input-group-btn">
                    <?php echo $view['form']->widget($form['start']); ?>
                </span>
            </div>
        <?php echo $view['form']->end($form); ?>
    </div>
</div>
