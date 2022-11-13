<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ('index' == $tmpl):
    $view->extend('MilexLeadBundle:Import:index.html.php');
endif;
?>

<?php if (count($items)): ?>
<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered" id="importTable">
        <thead>
            <tr>
                <?php
                echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => $sessionVar,
                    'orderBy'    => $tablePrefix.'.status',
                    'text'       => 'milex.lead.import.status',
                    'class'      => 'col-status',
                ]);

                echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => $sessionVar,
                    'orderBy'    => $tablePrefix.'.originalFile',
                    'text'       => 'milex.lead.import.source.file',
                    'class'      => 'col-original-file',
                ]);

                echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
                    'text'  => 'milex.lead.import.runtime',
                    'class' => 'col-runtime',
                ]);

                echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
                    'text'  => 'milex.lead.import.progress',
                    'class' => 'col-progress',
                ]);

                echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => $sessionVar,
                    'orderBy'    => $tablePrefix.'.lineCount',
                    'text'       => 'milex.lead.import.line.count',
                    'class'      => 'col-line-count',
                ]);

                echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => $sessionVar,
                    'orderBy'    => $tablePrefix.'.insertedCount',
                    'text'       => 'milex.lead.import.inserted.count',
                    'class'      => 'col-inserted-count',
                ]);

                echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => $sessionVar,
                    'orderBy'    => $tablePrefix.'.updatedCount',
                    'text'       => 'milex.lead.import.updated.count',
                    'class'      => 'col-updated-count',
                ]);

                echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => $sessionVar,
                    'orderBy'    => $tablePrefix.'.ignoredCount',
                    'text'       => 'milex.lead.import.ignored.count',
                    'class'      => 'col-ignored-count',
                ]);

                echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => $sessionVar,
                    'orderBy'    => $tablePrefix.'.createdByUser',
                    'text'       => 'milex.core.create.by.past.tense',
                    'class'      => 'col-created visible-md visible-lg',
                ]);

                echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => $sessionVar,
                    'orderBy'    => $tablePrefix.'.dateAdded',
                    'text'       => 'milex.core.date.added',
                    'class'      => 'col-date-added visible-md visible-lg',
                    'default'    => true,
                ]);

                echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => $sessionVar,
                    'orderBy'    => $tablePrefix.'.id',
                    'text'       => 'milex.core.id',
                    'class'      => 'col-lead-id visible-md visible-lg',
                ]);
                ?>
            </tr>
        </thead>
        <tbody>
        <?php echo $view->render('MilexLeadBundle:Import:list_rows.html.php', [
            'items'           => $items,
            'permissions'     => $permissions,
            'indexRoute'      => $indexRoute,
            'permissionBase'  => $permissionBase,
            'translationBase' => $translationBase,
            'actionRoute'     => $actionRoute,
        ]); ?>
        </tbody>
    </table>
</div>
<div class="panel-footer">
    <?php echo $view->render('MilexCoreBundle:Helper:pagination.html.php', [
        'totalItems' => $totalItems,
        'page'       => $page,
        'limit'      => $limit,
        'menuLinkId' => $indexRoute,
        'baseUrl'    => $view['router']->path($indexRoute, ['object' => $app->getRequest()->get('object', 'contacts')]),
        'sessionVar' => $sessionVar,
    ]); ?>
</div>
<?php else: ?>
<?php echo $view->render('MilexCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
