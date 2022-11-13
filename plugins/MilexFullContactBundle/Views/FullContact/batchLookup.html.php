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
    <div class="alert alert-info"><?php echo $view['translator']->trans('milex.plugin.fullcontact.submit_items'); ?></div>
    <div style="margin-top: 10px">
        <ul class="list-group" style="max-height: 400px;overflow-y: auto">
            <?php
            foreach ($lookupItems as $item) {
                echo '<li class="list-group-item">'.$item.'</li>';
            }
            ?>
        </ul>
    </div>

    <script>
        (function () {
            var ids = Milex.getCheckedListIds(false, true);
            if (mQuery('#fullcontact_batch_lookup_ids').length) {
                mQuery('#fullcontact_batch_lookup_ids').val(ids);
            }
        })();
    </script>
<?php
echo $view['form']->form($form, ['attr' => $attr]);
