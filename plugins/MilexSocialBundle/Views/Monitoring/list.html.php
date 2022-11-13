<?php

/*
 * @copyright   2016 Milex, Inc. All rights reserved
 * @author      Milex, Inc
 *
 * @link        https://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ('index' == $tmpl) {
    $view->extend('MilexSocialBundle:Monitoring:index.html.php');
}
?>
<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered monitoring-list" id="monitoringTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#monitoringTable',
                        'langVar'         => 'milex.social.monitoring',
                        'routeBase'       => 'social',
                        'templateButtons' => [
                            'delete' => $view['security']->isGranted('milexSocial:monitoring:delete'),
                        ],
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'social.monitoring',
                        'orderBy'    => 'e.title',
                        'text'       => 'milex.core.title',
                        'class'      => 'col-monitoring-title',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'social.monitoring',
                        'orderBy'    => 'e.id',
                        'text'       => 'milex.core.id',
                        'class'      => 'visible-md visible-lg col-asset-id',
                    ]
                );
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $k => $item): ?>
                <tr>
                    <td>
                        <?php
                        echo $view->render(
                            'MilexCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit'   => $view['security']->isGranted('milexSocial:monitoring:edit'),
                                    'delete' => $view['security']->isGranted('milexSocial:monitoring:delete'),
                                ],
                                'routeBase'  => 'social',
                                'langVar'    => 'milex.social.monitoring',
                                'nameGetter' => 'getTitle',
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <div>
                            <?php echo $view->render(
                                'MilexCoreBundle:Helper:publishstatus_icon.html.php',
                                [
                                    'item'  => $item,
                                    'model' => 'social.monitoring',
                                ]
                            ); ?>
                            <a href="<?php echo $view['router']->path(
                                'milex_social_action',
                                ['objectAction' => 'view', 'objectId' => $item->getId()]
                            ); ?>"
                               data-toggle="ajax">
                                <?php echo $item->getTitle(); ?>
                            </a>
                        </div>
                        <?php if ($description = $item->getDescription()): ?>
                            <div class="text-muted mt-4">
                                <small><?php echo $description; ?></small>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="panel-footer">
        <?php echo $view->render(
            'MilexCoreBundle:Helper:pagination.html.php',
            [
                'totalItems' => count($items),
                'page'       => $page,
                'limit'      => $limit,
                'menuLinkId' => 'milex_campaign_index',
                'baseUrl'    => $view['router']->path('milex_social_index'),
                'sessionVar' => 'social.monitoring',
                'routeBase'  => 'social',
            ]
        ); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MilexCoreBundle:Helper:noresults.html.php', ['tip' => 'milex.milex.social.monitoring.noresults.tip']); ?>
<?php endif; ?>

<?php echo $view->render(
    'MilexCoreBundle:Helper:modal.html.php',
    [
        'id'     => 'MonitoringPreviewModal',
        'header' => false,
    ]
);
