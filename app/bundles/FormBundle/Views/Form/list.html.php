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
    $view->extend('MilexFormBundle:Form:index.html.php');
}

?>
<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered" id="formTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#formTable',
                        'routeBase'       => 'form',
                        'templateButtons' => [
                            'delete' => $permissions['form:forms:deleteown'] || $permissions['form:forms:deleteother'],
                        ],
                        'customButtons' => [
                            [
                                'confirm' => [
                                    'message'       => $view['translator']->trans('milex.form.confirm_batch_rebuild'),
                                    'confirmText'   => $view['translator']->trans('milex.form.rebuild'),
                                    'confirmAction' => $view['router']->path(
                                        'milex_form_action',
                                        ['objectAction' => 'batchRebuildHtml']
                                    ),
                                    'iconClass'       => 'fa fa-fw fa-refresh',
                                    'btnText'         => $view['translator']->trans('milex.form.rebuild'),
                                    'precheck'        => 'batchActionPrecheck',
                                    'confirmCallback' => 'executeBatchAction',
                                ],
                                'primary' => true,
                            ],
                        ],
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'form',
                        'orderBy'    => 'f.name',
                        'text'       => 'milex.core.name',
                        'class'      => 'col-form-name',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'form',
                        'orderBy'    => 'c.title',
                        'text'       => 'milex.core.category',
                        'class'      => 'visible-md visible-lg col-form-category',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'form',
                        'orderBy'    => 'submission_count',
                        'text'       => 'milex.form.form.results',
                        'class'      => 'visible-md visible-lg col-form-submissions',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'form',
                        'orderBy'    => 'f.dateAdded',
                        'text'       => 'milex.lead.import.label.dateAdded',
                        'class'      => 'visible-md visible-lg col-form-dateAdded',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'form',
                        'orderBy'    => 'f.dateModified',
                        'text'       => 'milex.lead.import.label.dateModified',
                        'class'      => 'visible-md visible-lg col-form-dateModified',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'form',
                        'orderBy'    => 'f.createdByUser',
                        'text'       => 'milex.core.createdby',
                        'class'      => 'visible-md visible-lg col-form-createdby',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'form',
                        'orderBy'    => 'f.id',
                        'text'       => 'milex.core.id',
                        'class'      => 'visible-md visible-lg col-form-id',
                    ]
                );
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $i): ?>
                <?php $item = $i[0]; ?>
                <tr>
                    <td>
                        <?php
                        echo $view->render(
                            'MilexCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit' => $security->hasEntityAccess(
                                        $permissions['form:forms:editown'],
                                        $permissions['form:forms:editother'],
                                        $item->getCreatedBy()
                                    ),
                                    'clone'  => $permissions['form:forms:create'],
                                    'delete' => $security->hasEntityAccess(
                                        $permissions['form:forms:deleteown'],
                                        $permissions['form:forms:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                ],
                                'routeBase'     => 'form',
                                'customButtons' => [
                                    [
                                        'attr' => [
                                            'data-toggle' => '',
                                            'target'      => '_blank',
                                            'href'        => $view['router']->path(
                                                'milex_form_action',
                                                ['objectAction' => 'preview', 'objectId' => $item->getId()]
                                            ),
                                        ],
                                        'iconClass' => 'fa fa-camera',
                                        'btnText'   => 'milex.form.form.preview',
                                    ],
                                    [
                                        'attr' => [
                                            'data-toggle' => 'ajax',
                                            'href'        => $view['router']->path(
                                                'milex_form_action',
                                                ['objectAction' => 'results', 'objectId' => $item->getId()]
                                            ),
                                        ],
                                        'iconClass' => 'fa fa-database',
                                        'btnText'   => 'milex.form.form.results',
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
                                ['item' => $item, 'model' => 'form.form']
                            ); ?>
                            <a href="<?php echo $view['router']->path(
                                'milex_form_action',
                                ['objectAction' => 'view', 'objectId' => $item->getId()]
                            ); ?>" data-toggle="ajax" data-menu-link="milex_form_index">
                                <?php echo $item->getName(); ?>
                                <?php if ('campaign' == $item->getFormType()): ?>
                                    <span data-toggle="tooltip" title="<?php echo $view['translator']->trans(
                                        'milex.form.icon_tooltip.campaign_form'
                                    ); ?>"><i class="fa fa-fw fa-cube"></i></span>
                                <?php endif; ?>
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
                    <td class="visible-md visible-lg">
                        <a href="<?php echo $view['router']->path(
                            'milex_form_action',
                            ['objectAction' => 'results', 'objectId' => $item->getId()]
                        ); ?>" data-toggle="ajax" data-menu-link="milex_form_index" class="btn btn-primary btn-xs" <?php echo (0
                            == $i['submission_count']) ? 'disabled=disabled' : ''; ?>>
                            <?php echo $view['translator']->trans(
                                'milex.form.form.viewresults',
                                ['%count%' => $i['submission_count']]
                            ); ?>
                        </a>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getDateAdded() ? $view['date']->toFull($item->getDateAdded()) : ''; ?></td>
                    <td class="visible-md visible-lg"><?php echo $item->getDateModified() ? $view['date']->toFull($item->getDateModified()) : ''; ?></td>
                    <td class="visible-md visible-lg"><?php echo $item->getCreatedByUser(); ?></td>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="panel-footer">
            <?php echo $view->render(
                'MilexCoreBundle:Helper:pagination.html.php',
                [
                    'totalItems' => $totalItems,
                    'page'       => $page,
                    'limit'      => $limit,
                    'baseUrl'    => $view['router']->path('milex_form_index'),
                    'sessionVar' => 'form',
                ]
            ); ?>
        </div>
    </div>
<?php else: ?>
    <?php echo $view->render('MilexCoreBundle:Helper:noresults.html.php', ['tip' => 'milex.form.noresults.tip']); ?>
<?php endif; ?>
