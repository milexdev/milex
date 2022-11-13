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

<div class="well well-sm" style="margin-bottom:0 !important;">
    <p>
        <?php echo $view['translator']->trans('milex.plugin.clearbit.webhook_info'); ?>
    </p>
    <div class="alert alert-warning">
        <?php echo $view['translator']->trans('milex.plugin.clearbit.public_info'); ?>
    </div>
    <input type="text" readonly="" onclick="this.setSelectionRange(0, this.value.length);"
           value="<?php echo $milexUrl; ?>" class="form-control">
</div>
