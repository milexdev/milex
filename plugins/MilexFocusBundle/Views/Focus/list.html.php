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
    $view->extend('MilexFocusBundle:Focus:index.html.php');
}
?>

<?php if (count($items)): ?>
    <div class="table-responsive page-list">
        <table class="table table-hover table-striped table-bordered focus-list" id="focusTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#focusTable',
                        'routeBase'       => 'focus',
                        'templateButtons' => [
                            'delete' => $permissions['focus:items:delete'],
                        ],
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'focus',
                        'orderBy'    => 'f.name',
                        'text'       => 'milex.core.name',
                        'class'      => 'col-focus-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'focus',
                        'orderBy'    => 'c.title',
                        'text'       => 'milex.core.category',
                        'class'      => 'visible-md visible-lg col-focus-category',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'focus',
                        'orderBy'    => 'f.type',
                        'text'       => 'milex.focus.thead.type',
                        'class'      => 'visible-md visible-lg col-focus-type',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'focus',
                        'orderBy'    => 'f.style',
                        'text'       => 'milex.focus.thead.style',
                        'class'      => 'visible-md visible-lg col-focus-style',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'focus',
                        'orderBy'    => 'f.id',
                        'text'       => 'milex.core.id',
                        'class'      => 'visible-md visible-lg col-focus-id',
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
                        echo $view->render(
                            'MilexCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit' => $view['security']->hasEntityAccess(
                                        $permissions['focus:items:editown'],
                                        $permissions['focus:items:editother'],
                                        $item->getCreatedBy()
                                    ),
                                    'clone'  => $permissions['focus:items:create'],
                                    'delete' => $view['security']->hasEntityAccess(
                                        $permissions['focus:items:deleteown'],
                                        $permissions['focus:items:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                ],
                                'routeBase' => 'focus',
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <div>
                            <?php echo $view->render('MilexCoreBundle:Helper:publishstatus_icon.html.php', ['item' => $item, 'model' => 'focus']); ?>
                            <a data-toggle="ajax" href="<?php echo $view['router']->path(
                                'milex_focus_action',
                                ['objectId' => $item->getId(), 'objectAction' => 'view']
                            ); ?>">
                                <?php echo $item->getName(); ?>
                            </a>
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
                    <td class="visible-md visible-lg"><?php echo $view['translator']->trans('milex.focus.type.'.$item->getType()); ?></td>
                    <td class="visible-md visible-lg"><?php echo $view['translator']->trans('milex.focus.style.'.$item->getStyle()); ?></td>
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
                'baseUrl'    => $view['router']->path('milex_focus_index'),
                'sessionVar' => 'focus',
            ]
        ); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MilexCoreBundle:Helper:noresults.html.php', ['tip' => 'milex.focus.noresults.tip']); ?>
<?php endif; ?>
