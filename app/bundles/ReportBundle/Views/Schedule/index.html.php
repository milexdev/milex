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
    <div class="col-md-12">
        <?php
            foreach ($dates as $date) {
                echo $view['date']->toShort($date).'<br>';
            }
        ?>
    </div>
</div>
