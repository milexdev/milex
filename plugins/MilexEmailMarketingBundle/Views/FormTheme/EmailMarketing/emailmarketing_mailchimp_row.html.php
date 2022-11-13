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

<div class="alert alert-info">
    <?php echo $view['translator']->trans('milex.emailmarketing.list.update'); ?>
</div>
<div class="row">
    <div class="col-md-8">
        <?php echo $view['form']->row($form['list']); ?>
    </div>
</div>

<?php echo $view['form']->row($form['doubleOptin']); ?>
<?php echo $view['form']->row($form['sendWelcome']); ?>