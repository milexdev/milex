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
    $view->extend('MilexDynamicContentBundle:DynamicContent:index.html.php');
}
/* @var \Milex\DynamicContentBundle\Entity\DynamicContent[] $items */
?>

<?php if (count($items)): ?>
    <div class="table-responsive page-list">
        <table class="table table-hover table-striped table-bordered dwctable-list" id="dwcTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#dwcTable',
                        'routeBase'       => 'dynamicContent',
                        'templateButtons' => [
                            'delete' => $permissions['dynamiccontent:dynamiccontents:deleteown']
                                || $permissions['dynamiccontent:dynamiccontents:deleteother'],
                        ],
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'dynamicContent',
                        'orderBy'    => 'e.name',
                        'text'       => 'milex.core.name',
                        'class'      => 'col-dwc-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'dynamicContent',
                        'orderBy'    => 'e.slotName',
                        'text'       => 'milex.dynamicContent.label.slot_name',
                        'class'      => 'col-dwc-slotname visible-md visible-lg',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'dynamicContent',
                        'orderBy'    => 'c.title',
                        'text'       => 'milex.core.category',
                        'class'      => 'col-dwc-category visible-md visible-lg',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'dynamicContent',
                        'orderBy'    => 'e.id',
                        'text'       => 'milex.core.id',
                        'class'      => 'col-dwc-id visible-md visible-lg',
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
                                        $permissions['dynamiccontent:dynamiccontents:editown'],
                                        $permissions['dynamiccontent:dynamiccontents:editother'],
                                        $item->getCreatedBy()
                                    ),
                                    'clone'  => $permissions['dynamiccontent:dynamiccontents:create'],
                                    'delete' => $view['security']->hasEntityAccess(
                                        $permissions['dynamiccontent:dynamiccontents:deleteown'],
                                        $permissions['dynamiccontent:dynamiccontents:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                ],
                                'routeBase'  => 'dynamicContent',
                                'nameGetter' => 'getName',
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <?php echo $view->render(
                            'MilexCoreBundle:Helper:publishstatus_icon.html.php',
                            ['item' => $item, 'model' => 'dynamicContent']
                        ); ?>
                        <a href="<?php echo $view['router']->url(
                            'milex_dynamicContent_action',
                            ['objectAction' => 'view', 'objectId' => $item->getId()]
                        ); ?>" data-toggle="ajax">
                            <?php echo $item->getName(); ?>
                            <?php
                            $hasVariants     = $item->isVariant();
                            $hasTranslations = $item->isTranslation();
                            $isFilterBased   = !$item->getIsCampaignBased();

                            if ($hasVariants || $hasTranslations || $isFilterBased): ?>
                                <span>
                                <?php if ($hasVariants): ?>
                                    <span data-toggle="tooltip" title="<?php echo $view['translator']->trans('milex.core.icon_tooltip.ab_test'); ?>">
                                            <i class="fa fa-fw fa-sitemap"></i>
                                        </span>
                                <?php endif; ?>
                                    <?php if ($hasTranslations): ?>
                                        <span data-toggle="tooltip" title="<?php echo $view['translator']->trans(
                                            'milex.core.icon_tooltip.translation'
                                        ); ?>">
                                            <i class="fa fa-fw fa-language"></i>
                                        </span>
                                    <?php endif; ?>
                                 </span>
                            <?php endif; ?>
                        </a>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getSlotName(); ?></td>
                    <td class="visible-md visible-lg">
                        <?php $category = $item->getCategory(); ?>
                        <?php $catName  = ($category) ? $category->getTitle() : $view['translator']->trans('milex.core.form.uncategorized'); ?>
                        <?php $color    = ($category) ? '#'.$category->getColor() : 'inherit'; ?>
                        <span style="white-space: nowrap;"><span class="label label-default pa-4" style="border: 1px solid #d5d5d5; background: <?php echo $color; ?>;"> </span> <span><?php echo $catName; ?></span></span>
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
                    'menuLinkId' => 'milex_dynamicContent_index',
                    'baseUrl'    => $view['router']->url('milex_dynamicContent_index'),
                    'sessionVar' => 'dynamicContent',
                ]
            ); ?>
        </div>
    </div>
<?php else: ?>
    <?php echo $view->render('MilexCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
