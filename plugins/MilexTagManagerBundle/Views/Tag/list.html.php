<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
//Check to see if the entire page should be displayed or just main content
if ('index' == $tmpl):
    $view->extend('MilexTagManagerBundle:Tag:index.html.php');
endif;

if (!isset($nameGetter)) {
    $nameGetter = 'getTag';
}

$listCommand = $view['translator']->trans('milex.tagmanager.tag.searchcommand.list');
?>

<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered" id="tagsTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#tagsTable',
                        'langVar'         => 'tagmanager.tag',
                        'routeBase'       => 'tagmanager',
                        'templateButtons' => [
                            'delete' => $permissions['tagManager:tagManager:delete'],
                        ],
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'tags',
                        'orderBy'    => 'lt.tag',
                        'text'       => 'milex.core.name',
                        'class'      => 'col-tag-name',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'tags',
                        'text'       => 'milex.lead.list.thead.leadcount',
                        'class'      => 'visible-md visible-lg col-tag-leadcount',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'tags',
                        'orderBy'    => 'lt.id',
                        'text'       => 'milex.core.id',
                        'class'      => 'visible-md visible-lg col-tag-id',
                    ]
                );
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <?php $milexTemplateVars['item'] = $item; ?>
                <tr>
                    <td>
                        <?php
                        echo $view->render(
                            'MilexCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit'   => $permissions['tagManager:tagManager:edit'],
                                    'delete' => $permissions['tagManager:tagManager:delete'],
                                ],
                                'routeBase'  => 'tagmanager',
                                'langVar'    => 'tagmanager.tag',
                                'nameGetter' => $nameGetter,
                                'custom'     => [
                                    [
                                        'attr' => [
                                            'data-toggle' => 'ajax',
                                            'href'        => $view['router']->path(
                                                'milex_contact_index',
                                                [
                                                    'search' => "$listCommand:{$item->getTag()}",
                                                ]
                                            ),
                                        ],
                                        'icon'  => 'fa-users',
                                        'label' => 'milex.lead.list.view_contacts',
                                    ],
                                ],
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <div>
                            <?php if ($permissions['tagManager:tagManager:edit']) : ?>
                                <a href="<?php echo $view['router']->path(
                                    'milex_tagmanager_action',
                                    ['objectAction' => 'view', 'objectId' => $item->getId()]
                                ); ?>" data-toggle="ajax">
                                    <?php echo $item->getTag(); ?>
                                </a>
                            <?php else : ?>
                                <?php echo $item->getTag(); ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($description = $item->getDescription()): ?>
                            <div class="text-muted mt-4">
                                <small><?php echo $description; ?></small>
                            </div>
                        <?php endif; ?>
                    </td>

                    <td class="visible-md visible-lg">
                        <a class="label label-primary" href="<?php echo $view['router']->path(
                            'milex_contact_index',
                            ['search' => $view['translator']->trans('milex.tagmanager.lead.searchcommand.list').':"'.$item->getTag().'"']
                        ); ?>" data-toggle="ajax"<?php echo (0 == $tagsCount[$item->getId()]) ? 'disabled=disabled' : ''; ?>>
                            <?php echo $view['translator']->trans(
                                'milex.lead.list.viewleads_count',
                                ['%count%' => $tagsCount[$item->getId()]]
                            ); ?>
                        </a>
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
                    'baseUrl'    => $view['router']->path('milex_tagmanager_index'),
                    'sessionVar' => 'tagmanager',
                ]
            ); ?>
        </div>
    </div>
<?php else: ?>
    <?php echo $view->render('MilexCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
