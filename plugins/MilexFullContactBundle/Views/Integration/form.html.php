<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

echo $view['assets']->includeScript('plugins/MilexFullContactBundle/Assets/js/fullcontact.js');

?>

<div class="well well-sm" style="margin-bottom:0 !important;">
    <p><?php echo $view['translator']->trans('milex.plugin.fullcontact.webhook'); ?></p>
    <div class="alert alert-warning">
        <?php echo $view['translator']->trans('milex.plugin.fullcontact.public_info'); ?>
    </div>
    <input type="text" readonly="readonly" value="<?php echo $milexUrl; ?>" class="form-control" title="url"/>
</div>
