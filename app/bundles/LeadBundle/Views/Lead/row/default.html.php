<?php

/*
 * @copyright   2019 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

?>
<td class="<?php echo $class; ?>">
    <?php
    foreach ($fields as $field) {
        if (isset($field[$column]['value'])) {
            echo $view->escape($view['formatter']->normalizeStringValue($field[$column]['normalizedValue']));
        }
    }
    ?>
</td>
