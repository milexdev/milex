<?php

/*
 * @copyright   2015 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<div class="row">
<?php foreach ($form as $child): ?>
    <div class="col-sm-6">
        <?php echo $view['form']->row($child); ?>
    </div>
<?php endforeach; ?>
</div>