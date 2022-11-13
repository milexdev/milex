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
    $view->extend('MilexAssetBundle:Asset:index.html.php');
}
?>
<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered asset-list" id="assetTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#assetTable',
                        'langVar'         => 'asset.asset',
                        'routeBase'       => 'asset',
                        'templateButtons' => [
                            'delete' => $permissions['asset:assets:deleteown'] || $permissions['asset:assets:deleteother'],
                        ],
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'asset',
                        'orderBy'    => 'a.title',
                        'text'       => 'milex.core.title',
                        'class'      => 'col-asset-title',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'asset',
                        'orderBy'    => 'c.title',
                        'text'       => 'milex.core.category',
                        'class'      => 'visible-md visible-lg col-asset-category',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'asset',
                        'orderBy'    => 'a.downloadCount',
                        'text'       => 'milex.asset.asset.thead.download.count',
                        'class'      => 'visible-md visible-lg col-asset-download-count',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'asset',
                        'orderBy'    => 'a.dateAdded',
                        'text'       => 'milex.lead.import.label.dateAdded',
                        'class'      => 'visible-md visible-lg col-asset-dateAdded',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'asset',
                        'orderBy'    => 'a.dateModified',
                        'text'       => 'milex.lead.import.label.dateModified',
                        'class'      => 'visible-md visible-lg col-asset-dateModified',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'asset',
                        'orderBy'    => 'a.createdByUser',
                        'text'       => 'milex.core.createdby',
                        'class'      => 'visible-md visible-lg col-asset-createdByUser',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'asset',
                        'orderBy'    => 'a.id',
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
                                    'edit' => $security->hasEntityAccess(
                                        $permissions['asset:assets:editown'],
                                        $permissions['asset:assets:editother'],
                                        $item->getCreatedBy()
                                    ),
                                    'delete' => $security->hasEntityAccess(
                                        $permissions['asset:assets:deleteown'],
                                        $permissions['asset:assets:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                    'clone' => $permissions['asset:assets:create'],
                                ],
                                'routeBase'     => 'asset',
                                'langVar'       => 'asset.asset',
                                'nameGetter'    => 'getTitle',
                                'customButtons' => [
                                    [
                                        'attr' => [
                                            'data-toggle' => 'ajaxmodal',
                                            'data-target' => '#AssetPreviewModal',
                                            'href'        => $view['router']->path(
                                                'milex_asset_action',
                                                ['objectAction' => 'preview', 'objectId' => $item->getId()]
                                            ),
                                        ],
                                        'btnText'   => $view['translator']->trans('milex.asset.asset.preview'),
                                        'iconClass' => 'fa fa-image',
                                    ],
                                ],
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
                                    'model' => 'asset.asset',
                                ]
                            ); ?>
                            <a href="<?php echo $view['router']->path(
                                'milex_asset_action',
                                ['objectAction' => 'view', 'objectId' => $item->getId()]
                            ); ?>"
                               data-toggle="ajax">
                                <?php echo $item->getTitle(); ?> (<?php echo $item->getAlias(); ?>)
                            </a>
                            <i class="<?php echo $item->getIconClass(); ?>"></i>
                        </div>
                        <?php if ($description = $item->getDescription()): ?>
                            <div class="text-muted mt-4">
                                <small><?php echo $description; ?></small>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="visible-md visible-lg">
                        <?php $category = $item->getCategory(); ?>
                        <?php $catName  = ($category) ? $category->getTitle() : $view['translator']->trans('milex.core.form.uncategorized'); ?>
                        <?php $color    = ($category) ? '#'.$category->getColor() : 'inherit'; ?>
                        <span style="white-space: nowrap;"><span class="label label-default pa-4" style="border: 1px solid #d5d5d5; background: <?php echo $color; ?>;"> </span> <span><?php echo $catName; ?></span></span>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getDownloadCount(); ?></td>
                    <td class="visible-md visible-lg" title="<?php echo $item->getDateAdded() ? $view['date']->toFullConcat($item->getDateAdded()) : ''; ?>">
                        <?php echo $item->getDateAdded() ? $view['date']->toDate($item->getDateAdded()) : ''; ?>
                    </td>
                    <td class="visible-md visible-lg" title="<?php echo $item->getDateModified() ? $view['date']->toFullConcat($item->getDateModified()) : ''; ?>">
                        <?php echo $item->getDateModified() ? $view['date']->toDate($item->getDateModified()) : ''; ?>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getCreatedByUser(); ?></td>
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
                'menuLinkId' => 'milex_asset_index',
                'baseUrl'    => $view['router']->path('milex_asset_index'),
                'sessionVar' => 'asset',
            ]
        ); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MilexCoreBundle:Helper:noresults.html.php', ['tip' => 'milex.asset.noresults.tip']); ?>
<?php endif; ?>

<?php echo $view->render(
    'MilexCoreBundle:Helper:modal.html.php',
    [
        'id'     => 'AssetPreviewModal',
        'header' => false,
    ]
);
