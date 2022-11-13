<?php

/*
 * @copyright   2016 Milex Contributors. All rights reserved
 * @author      Milex, Inc.
 *
 * @link        https://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

?>

<div class="row">
    <div class="col-xs-12">
        <button type="button" class="btn btn-primary btn-apply-builder">
            <?php echo $view['translator']->trans('milex.core.form.apply'); ?>
        </button>
        <button type="button" class="btn btn-primary btn-close-builder" onclick="<?php echo $onclick; ?>">
            <?php echo $view['translator']->trans('milex.core.close.builder'); ?>
        </button>
    </div>
    <!--
    <div class="col-xs-6 text-right">
        <button type="button" class="btn btn-default btn-undo btn-nospin" data-toggle="tooltip" data-placement="left" title="<?php echo $view['translator']->trans('milex.core.undo'); ?>">
            <span><i class="fa fa-undo"></i></span>
        </button>
        <button type="button" class="btn btn-default btn-redo btn-nospin" data-toggle="tooltip" data-placement="left" title="<?php echo $view['translator']->trans('milex.core.redo'); ?>">
            <span><i class="fa fa-repeat"></i></span>
        </button>
    </div>
    -->
</div>
<div class="row">
    <div class="col-xs-12 mt-15">
        <div id="builder-errors" class="alert alert-danger" role="alert" style="display: none;"></div>
    </div>
</div>
