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
    $view->extend('MilexApiBundle:Client:index.html.php');
endif;
?>

<div class="table-responsive panel-collapse pull out page-list">
    <table class="table table-hover table-striped table-bordered client-list">
        <thead>
        <tr>
            <?php
            echo $view->render(
                'MilexCoreBundle:Helper:tableheader.html.php',
                [
                    'checkall'        => 'true',
                    'target'          => '.client-list',
                    'action'          => $currentRoute,
                    'routeBase'       => 'client',
                    'templateButtons' => [],
                ]
            );

            echo $view->render(
                'MilexCoreBundle:Helper:tableheader.html.php',
                [
                    'sessionVar' => 'client',
                    'orderBy'    => 'c.name',
                    'text'       => 'milex.core.name',
                    'default'    => true,
                    'class'      => 'col-client-name',
                ]
            );
            ?>
            <th class="visible-md visible-lg col-client-publicid"><?php echo $view['translator']->trans('milex.api.client.thead.publicid'); ?></th>
            <th class="visible-md visible-lg col-client-secret"><?php echo $view['translator']->trans('milex.api.client.thead.secret'); ?></th>
            <?php
            echo $view->render(
                'MilexCoreBundle:Helper:tableheader.html.php',
                [
                    'sessionVar' => 'client',
                    'orderBy'    => 'c.id',
                    'text'       => 'milex.core.id',
                    'class'      => 'visible-md visible-lg col-client-id',
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
                                'edit'   => $permissions['edit'],
                                'delete' => $permissions['delete'],
                            ],
                            'routeBase' => 'client',
                            'langVar'   => 'api.client',
                            'pull'      => 'left',
                        ]
                    );
                    ?>
                </td>
                <td>
                    <?php echo $item->getName(); ?>
                </td>
                <td class="visible-md visible-lg">
                    <input onclick="this.setSelectionRange(0, this.value.length);" type="text" class="form-control" readonly value="<?php echo $view->escape($item->getPublicId()); ?>"/>
                </td>
                <td class="visible-md visible-lg">
                    <input onclick="this.setSelectionRange(0, this.value.length);" type="text" class="form-control" readonly value="<?php echo $view->escape($item->getSecret()); ?>"/>
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
                'baseUrl'    => $view['router']->path('milex_client_index'),
                'sessionVar' => 'client',
                'tmpl'       => $tmpl,
            ]
        ); ?>
    </div>
</div>
