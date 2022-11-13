<?php

/*
 * @copyright   2015 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$attr = $form->vars['attr'];
?>
    <div class="alert alert-info"><?php echo $view['translator']->trans('milex.plugin.clearbit.submit'); ?></div>
    <div style="margin-top: 10px">
        <ul class="list-group" style="max-height: 400px;overflow-y: auto">
            <?php
            echo '<li class="list-group-item">'.$lookupItem.'</li>';
            ?>
        </ul>
    </div>
<?php
echo $view['form']->form($form, ['attr' => $attr]);
