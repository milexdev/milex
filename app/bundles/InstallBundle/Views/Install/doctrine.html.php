<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ('index' == $tmpl) {
    $view->extend('MilexInstallBundle:Install:content.html.php');
}

?>

<div class="panel-heading">
    <h2 class="panel-title">
        <?php echo $view['translator']->trans('milex.install.heading.database.configuration'); ?>
    </h2>
</div>
<div class="panel-body">
    <?php echo $view['form']->start($form); ?>
    <div class="alert alert-milex">
        <h6><?php echo $view['translator']->trans('milex.install.database.introtext'); ?></h6>
    </div>

    <?php echo $view['form']->row($form['driver']); ?>

    <?php $driver = $form['driver']->vars['data']; ?>
    <div id="DatabaseSettings"<?php if ('pdo_sqlite' == $driver) {
    echo ' class="hide"';
} ?>>
        <div class="row">
            <div class="col-sm-6">
                <?php echo $view['form']->row($form['host']); ?>
            </div>
            <div class="col-sm-6">
                <?php echo $view['form']->row($form['port']); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <?php echo $view['form']->row($form['name']); ?>
            </div>
            <div class="col-sm-6">
                <?php echo $view['form']->row($form['table_prefix']); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <?php echo $view['form']->row($form['user']); ?>
            </div>
            <div class="col-sm-6">
                <?php echo $view['form']->row($form['password']); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <?php echo $view['form']->row($form['backup_tables']); ?>
        </div>
        <?php $hide = (!$form['backup_tables']->vars['data']) ? ' hide' : ''; ?>
        <div class="col-sm-6<?php echo $hide; ?>" id="backupPrefix">
            <?php echo $view['form']->row($form['backup_prefix']); ?>
        </div>
    </div>

    <div class="row mt-20">
        <div class="col-sm-9">
            <div class="hide" id="waitMessage">
                <div class="alert alert-info">
                    <strong><?php echo $view['translator']->trans('milex.install.database.installing'); ?></strong>
                </div>
            </div>
            <?php echo $view->render('MilexInstallBundle:Install:navbar.html.php', ['step' => $index, 'count' => $count, 'completedSteps' => $completedSteps]); ?>
        </div>
        <div class="col-sm-3">
            <?php echo $view['form']->row($form['buttons']); ?>
        </div>
    </div>
    <?php echo $view['form']->end($form); ?>
</div>
