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
    $view->extend('MilexPointBundle:Trigger:index.html.php');
}
?>

<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered pointtrigger-list" id="triggerTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#triggerTable',
                        'langVar'         => 'point.trigger',
                        'routeBase'       => 'pointtrigger',
                        'templateButtons' => [
                            'delete' => $permissions['point:triggers:delete'],
                        ],
                    ]
                );

                echo "<th class='col-pointtrigger-color'></th>";

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'point.trigger',
                        'orderBy'    => 't.name',
                        'text'       => 'milex.core.name',
                        'class'      => 'col-pointtrigger-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'pointtrigger',
                        'orderBy'    => 'cat.title',
                        'text'       => 'milex.core.category',
                        'class'      => 'col-pointtrigger-category visible-md visible-lg',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'point.trigger',
                        'orderBy'    => 't.points',
                        'text'       => 'milex.point.trigger.thead.points',
                        'class'      => 'col-pointtrigger-points',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'point.trigger',
                        'orderBy'    => 't.id',
                        'text'       => 'milex.core.id',
                        'class'      => 'col-pointtrigger-id visible-md visible-lg',
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
                                    'edit'   => $permissions['point:triggers:edit'],
                                    'clone'  => $permissions['point:triggers:create'],
                                    'delete' => $permissions['point:triggers:delete'],
                                ],
                                'routeBase' => 'pointtrigger',
                                'langVar'   => 'point.trigger',
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
                                ['item' => $item, 'model' => 'point.trigger']
                            ); ?>
                            <?php if ($permissions['point:triggers:edit']): ?>
                                <a href="<?php echo $view['router']->path(
                                    'milex_pointtrigger_action',
                                    ['objectAction' => 'edit', 'objectId' => $item->getId()]
                                ); ?>" data-toggle="ajax">
                                    <?php echo $item->getName(); ?>
                                </a>
                            <?php else: ?>
                                <?php echo $item->getName(); ?>
                            <?php endif; ?>
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
                    <td><?php echo $item->getPoints(); ?></td>
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
                'menuLinkId' => 'milex_pointtrigger_index',
                'baseUrl'    => $view['router']->path('milex_pointtrigger_index'),
                'sessionVar' => 'pointtrigger',
            ]
        ); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MilexCoreBundle:Helper:noresults.html.php', ['tip' => 'milex.point.trigger.noresults.tip']); ?>
<?php endif; ?>
