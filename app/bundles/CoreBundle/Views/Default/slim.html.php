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
<!DOCTYPE html>
<html>
    <?php echo $view->render('MilexCoreBundle:Default:head.html.php'); ?>
    <body>
        <?php $view['assets']->outputScripts('bodyOpen'); ?>
        <section id="app-content" class="container content-only">
            <?php echo $view->render('MilexCoreBundle:Notification:flashes.html.php', ['alertType' => 'standard']); ?>
            <?php $view['slots']->output('_content'); ?>
        </section>
        <?php echo $view->render('MilexCoreBundle:Helper:modal.html.php', [
            'id'            => 'MilexSharedModal',
            'footerButtons' => true,
        ]); ?>
        <?php $view['assets']->outputScripts('bodyClose'); ?>
        <script>
            Milex.onPageLoad('body');
        </script>
    </body>
</html>
