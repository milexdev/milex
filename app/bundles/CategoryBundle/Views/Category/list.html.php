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
    $view->extend('MilexCategoryBundle:Category:index.html.php');
}
?>

<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered category-list" id="categoryTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#categoryTable',
                        'routeBase'       => 'category',
                        'templateButtons' => [
                            'delete' => $permissions[$permissionBase.':delete'],
                        ],
                        'query' => [
                            'bundle' => $bundle,
                        ],
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'category',
                        'text'       => '',
                        'class'      => 'col-category-color',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'category',
                        'orderBy'    => 'c.title',
                        'text'       => 'milex.core.title',
                        'class'      => 'col-category-title',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'category',
                        'orderBy'    => 'c.bundle',
                        'text'       => 'milex.core.type',
                        'class'      => 'visible-md visible-lg col-page-bundle',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'category',
                        'orderBy'    => 'c.id',
                        'text'       => 'milex.core.id',
                        'class'      => 'visible-md visible-lg col-page-id',
                    ]
                );
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <?php
                        $bundleName = $view['translator']->trans('milex.'.$item->getBundle().'.'.$item->getBundle());
                        $title      = $view['translator']->trans(
                            'milex.category.header.edit',
                            ['%type%' => $bundleName, '%name%' => $item->getTitle()]
                        );
                        echo $view->render(
                            'MilexCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit'   => $permissions[$permissionBase.':edit'],
                                    'delete' => $permissions[$permissionBase.':delete'],
                                ],
                                'editMode' => 'ajaxmodal',
                                'editAttr' => [
                                    'data-target' => '#MilexSharedModal',
                                    'data-header' => $title,
                                    'data-toggle' => 'ajaxmodal',
                                ],
                                'routeBase' => 'category',
                                'query'     => [
                                    'bundle' => $bundle,
                                ],
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <span class="label label-default pa-10" style="background: #<?php echo $item->getColor(); ?>;"> </span>
                    </td>
                    <td>
                        <div>
                            <?php echo $view->render(
                                'MilexCoreBundle:Helper:publishstatus_icon.html.php',
                                ['item' => $item, 'model' => 'category', 'query' => 'bundle='.$bundle]
                            ); ?>
                            <?php if ($permissions[$permissionBase.':edit']): ?>
                                <a href="<?php echo $view['router']->path(
                                    'milex_category_action',
                                    ['bundle' => $bundle, 'objectAction' => 'edit', 'objectId' => $item->getId()]
                                ); ?>" data-toggle="ajaxmodal" data-target="#MilexSharedModal" data-header="<?php echo $title; ?>"
                            <?php endif; ?>
                            <span><?php echo $item->getTitle(); ?> (<?php echo $item->getAlias(); ?>)</span>
                            <?php if ($permissions[$permissionBase.':edit']): ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php if ($description = $item->getDescription()): ?>
                            <div class="text-muted mt-4">
                                <small><?php echo $description; ?></small>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="visible-md visible-lg">
                        <?php if (isset($categoryTypes[$item->getBundle()])) : ?>
                            <?php echo $view['translator']->trans($categoryTypes[$item->getBundle()]); ?>
                        <?php endif; ?>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="panel-footer">
            <?php echo $view->render(
                'MilexCoreBundle:Helper:pagination.html.php',
                [
                    'totalItems' => count($items),
                    'page'       => $page,
                    'limit'      => $limit,
                    'menuLinkId' => 'milex_category_index',
                    'baseUrl'    => $view['router']->path(
                        'milex_category_index',
                        [
                            'bundle' => ('category' == $bundle) ? 'all' : $bundle,
                        ]
                    ),
                    'sessionVar' => 'category',
                ]
            ); ?>
        </div>
    </div>
<?php else: ?>
    <?php echo $view->render('MilexCoreBundle:Helper:noresults.html.php', ['tip' => 'milex.category.noresults.tip']); ?>
<?php endif; ?>
