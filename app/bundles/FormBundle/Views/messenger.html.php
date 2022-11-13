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
<html>
    <head>
        <script type="text/javascript">
            parent.postMessage("<?php echo $view->escape($response, 'js'); ?>", '*');
        </script>

        <?php echo $view['analytics']->getCode(); ?>
    </head>

    <body></body>
</html>