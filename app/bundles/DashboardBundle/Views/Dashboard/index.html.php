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
$view['slots']->set('headerTitle', $view['translator']->trans('milex.dashboard.header.index'));
$view['slots']->set('milexContent', 'dashboard');

$buttons = [
    [
        'attr' => [
            'class'       => 'btn btn-default btn-nospin',
            'data-toggle' => 'ajaxmodal',
            'data-target' => '#MilexSharedModal',
            'href'        => $view['router']->path('milex_dashboard_action', ['objectAction' => 'new']),
            'data-header' => $view['translator']->trans('milex.dashboard.widget.add'),
        ],
        'iconClass' => 'fa fa-plus',
        'btnText'   => 'milex.dashboard.widget.add',
    ],
    [
        'attr' => [
            'class'       => 'btn btn-default btn-nospin',
            'href'        => 'javascript:void()',
            'onclick'     => "Milex.saveDashboardLayout('{$view['translator']->trans('milex.dashboard.confirmation_layout_name')}');",
            'data-toggle' => '',
        ],
        'iconClass' => 'fa fa-save',
        'btnText'   => 'milex.core.form.save',
    ],
    [
        'attr' => [
            'class'       => 'btn btn-default btn-nospin',
            'href'        => 'javascript:void()',
            'onclick'     => "Milex.exportDashboardLayout('{$view['translator']->trans('milex.dashboard.confirmation_layout_name')}', '{$view['router']->path('milex_dashboard_action', ['objectAction' => 'export'])}');",
            'data-toggle' => '',
        ],
        'iconClass' => 'fa fa-cloud-download',
        'btnText'   => 'milex.dashboard.export.widgets',
    ],
    [
        'attr' => [
            'class'       => 'btn btn-default',
            'href'        => $view['router']->path('milex_dashboard_action', ['objectAction' => 'import']),
            'data-header' => $view['translator']->trans('milex.dashboard.widget.import'),
        ],
        'iconClass' => 'fa fa-cloud-upload',
        'btnText'   => 'milex.dashboard.widget.import',
    ],
];

$view['slots']->set('actions', $view->render('MilexCoreBundle:Helper:page_actions.html.php', [
    'routeBase'     => 'dashboard',
    'langVar'       => 'dashboard',
    'customButtons' => $buttons,
]));
?>
<?php if (true === $phpVersion['isOutdated']): ?>
<div class="pt-md pl-md col-md-12">
    <div class="pt-md pl-md alert alert-warning">
        <h3><?php echo $view['translator']->trans('milex.dashboard.phpversionwarning.title'); ?></h3>
        <p><?php echo $view['translator']->trans('milex.dashboard.phpversionwarning.body', ['%phpversion%' => $phpVersion['version']]); ?></p>
    </div>
</div>
<?php endif; ?>
<div class="row pt-md pl-md">
    <div class="col-sm-6">
        <?php echo $view->render('MilexCoreBundle:Helper:graph_dateselect.html.php', ['dateRangeForm' => $dateRangeForm]); ?>
    </div>
</div>

<?php if (count($widgets)): ?>
    <div id="dashboard-widgets" class="dashboard-widgets cards">
        <?php foreach ($widgets as $widget): ?>
            <div class="card-flex widget" data-widget-id="<?php echo $widget->getId(); ?>" style="width: <?php echo $widget->getWidth() ? $widget->getWidth().'' : '100'; ?>%; height: <?php echo $widget->getHeight() ? $widget->getHeight().'px' : '300px'; ?>">
                <div class="spinner"><i class="fa fa-spin fa-spinner"></i></div>
            </div>
        <?php endforeach; ?>
    </div>
    <div id="cloned-widgets" class="dashboard-widgets cards"></div>
<?php else: ?>
    <div class="well well col-md-6 col-md-offset-3 mt-md">
        <div class="row">
            <div class="mautibot-image col-xs-3 text-center">
                <img class="img-responsive" style="max-height: 125px; margin-left: auto; margin-right: auto;" src="<?php echo $view['mautibot']->getImage('wave'); ?>" />
            </div>
            <div class="col-xs-9">
                <h4><i class="fa fa-quote-left"></i> <?php echo $view['translator']->trans('milex.dashboard.nowidgets.tip.header'); ?> <i class="fa fa-quote-right"></i></h4>
                <p class="mt-md"><?php echo $view['translator']->trans('milex.dashboard.nowidgets.tip'); ?></p>
                <a href="<?php echo $view['router']->path('milex_dashboard_action', ['objectAction' => 'applyDashboardFile', 'file' => 'default.json']); ?>" class="btn btn-success">
                    Apply the default dashboard
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
