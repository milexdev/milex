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
    $view->extend('MilexSmsBundle:Sms:index.html.php');
}

if (count($items)):

    ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered sms-list">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'routeBase'       => 'sms',
                        'templateButtons' => [
                            'delete' => $permissions['sms:smses:deleteown'] || $permissions['sms:smses:deleteother'],
                        ],
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'sms',
                        'orderBy'    => 'e.name',
                        'text'       => 'milex.core.name',
                        'class'      => 'col-sms-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'sms',
                        'orderBy'    => 'c.title',
                        'text'       => 'milex.core.category',
                        'class'      => 'visible-md visible-lg col-sms-category',
                    ]
                );
                ?>

                <th class="visible-sm visible-md visible-lg col-sms-stats"><?php echo $view['translator']->trans('milex.core.stats'); ?></th>

                <?php
                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'sms',
                        'orderBy'    => 'e.id',
                        'text'       => 'milex.core.id',
                        'class'      => 'visible-md visible-lg col-sms-id',
                    ]
                );
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            /** @var \Milex\SmsBundle\Entity\Sms $item */
            foreach ($items as $item):
                $type = $item->getSmsType();
                ?>
                <tr>
                    <td>
                        <?php
                        $edit = $view['security']->hasEntityAccess(
                            $permissions['sms:smses:editown'],
                            $permissions['sms:smses:editother'],
                            $item->getCreatedBy()
                        );
                        $customButtons = [
                            [
                                'attr' => [
                                    'data-toggle' => 'ajaxmodal',
                                    'data-target' => '#MilexSharedModal',
                                    'data-header' => $view['translator']->trans('milex.sms.smses.header.preview'),
                                    'data-footer' => 'false',
                                    'href'        => $view['router']->path(
                                        'milex_sms_action',
                                        ['objectId' => $item->getId(), 'objectAction' => 'preview']
                                    ),
                                ],
                                'btnText'   => $view['translator']->trans('milex.sms.preview'),
                                'iconClass' => 'fa fa-share',
                            ],
                        ];
                        echo $view->render(
                            'MilexCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit' => $view['security']->hasEntityAccess(
                                        $permissions['sms:smses:editown'],
                                        $permissions['sms:smses:editother'],
                                        $item->getCreatedBy()
                                    ),
                                    'clone'  => $permissions['sms:smses:create'],
                                    'delete' => $view['security']->hasEntityAccess(
                                        $permissions['sms:smses:deleteown'],
                                        $permissions['sms:smses:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                ],
                                'routeBase'     => 'sms',
                                'customButtons' => $customButtons,
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <div>
                            <?php echo $view->render(
                                'MilexCoreBundle:Helper:publishstatus_icon.html.php',
                                ['item' => $item, 'model' => 'sms']
                            ); ?>
                            <a href="<?php echo $view['router']->path(
                                'milex_sms_action',
                                ['objectAction' => 'view', 'objectId' => $item->getId()]
                            ); ?>">
                                <?php echo $item->getName(); ?>
                                <?php if ('list' == $type): ?>
                                    <span data-toggle="tooltip" title="<?php echo $view['translator']->trans('milex.sms.icon_tooltip.list_sms'); ?>"><i class="fa fa-fw fa-pie-chart"></i></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </td>
                    <td class="visible-md visible-lg">
                        <?php $category = $item->getCategory(); ?>
                        <?php $catName  = ($category) ? $category->getTitle() : $view['translator']->trans('milex.core.form.uncategorized'); ?>
                        <?php $color    = ($category) ? '#'.$category->getColor() : 'inherit'; ?>
                        <span style="white-space: nowrap;"><span class="label label-default pa-4" style="border: 1px solid #d5d5d5; background: <?php echo $color; ?>;"> </span> <span><?php echo $catName; ?></span></span>
                    </td>
                       <?php
                       echo $view->render(
                           'MilexSmsBundle:Sms:list_stats.html.php',
                           [
                               'item' => $item,
                           ]
                       );
                       ?>
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
                'totalItems' => $totalItems,
                'page'       => $page,
                'limit'      => $limit,
                'baseUrl'    => $view['router']->path('milex_sms_index'),
                'sessionVar' => 'sms',
            ]
        ); ?>
    </div>
<?php elseif (!$configured): ?>
    <?php echo $view->render(
        'MilexCoreBundle:Helper:noresults.html.php',
        ['header' => 'milex.sms.disabled', 'message' => 'milex.sms.enable.in.configuration']
    ); ?>
<?php else: ?>
    <?php echo $view->render('MilexCoreBundle:Helper:noresults.html.php', ['message' => 'milex.sms.create.in.campaign.builder']); ?>
<?php endif; ?>
