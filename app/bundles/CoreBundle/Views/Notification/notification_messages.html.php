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

<?php if (!empty($updateMessage['message'])) : ?>
<div class="media pt-sm pb-sm pr-md pl-md nm bdr-b alert-milex milex-update">
    <h4 class="pull-left"><?php echo $updateMessage['message']; ?></h4>
    <div class="pull-right">
        <a class="btn btn-danger" href="<?php echo $view['router']->path('milex_core_update'); ?>" data-toggle="ajax"><?php echo $view['translator']->trans('milex.core.update.now'); ?></a>
    </div>
    <div class="clearfix"></div>
</div>
<?php endif; ?>
<?php foreach ($notifications as $n): ?>
    <?php echo $view->render('MilexCoreBundle:Notification:notification.html.php', ['n' => $n]); ?>
<?php endforeach; ?>