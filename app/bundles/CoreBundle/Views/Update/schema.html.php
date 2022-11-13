<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MilexCoreBundle:Default:content.html.php');
$view['slots']->set('milexContent', 'update');
$view['slots']->set('headerTitle', $view['translator']->trans('milex.core.update.index'));

if ($failed) {
    $message = $view['translator']->trans('milex.core.update.error_performing_migration');
    $class   = 'danger';
} elseif ($noMigrations) {
    $message = $view['translator']->trans('milex.core.update.schema_uptodate');
    $class   = 'milex';
} else {
    $message = $view['translator']->trans('milex.core.update.schema_updated');
    $class   = 'success';
}
?>

<div class="panel panel-default mnb-5 bdr-t-wdh-0">
    <div id="update-panel" class="panel-body">
        <div class="col-sm-offset-2 col-sm-8">
            <div class="alert alert-<?php echo $class; ?> mb-sm">
                <?php echo $view['translator']->trans($message); ?>
            </div>
            <?php if (!$failed): ?>
                <div class="text-center">
                    <a href="<?php echo $view['router']->path('milex_dashboard_index'); ?>" data-toggle="ajax"><?php echo $view['translator']->trans('milex.core.go_to_dashboard'); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
