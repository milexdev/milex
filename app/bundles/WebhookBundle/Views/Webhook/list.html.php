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
    $view->extend('MilexWebhookBundle:Webhook:index.html.php');
}
?>

<?php if (count($items)): ?>
    <div class="table-responsive panel-collapse pull out webhook-list">
        <table class="table table-hover table-striped table-bordered webhook-list" id="webhookTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#webhookTable',
                        'routeBase'       => 'webhook',
                        'templateButtons' => [
                            'delete' => $permissions['webhook:webhooks:deleteown'] || $permissions['webhook:webhooks:deleteother'],
                        ],
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'milex_webhook',
                        'orderBy'    => 'e.name',
                        'text'       => 'milex.core.name',
                        'class'      => 'col-webhook-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'milex_webhook',
                        'orderBy'    => 'e.webhookUrl',
                        'text'       => 'milex.webhook.webhook_url',
                        'class'      => 'col-webhook-id visible-md visible-lg',
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'milex_webhook',
                        'orderBy'    => 'e.id',
                        'text'       => 'milex.core.id',
                        'class'      => 'col-webhook-id visible-md visible-lg',
                    ]
                );
                ?>
            </tr>
            </thead>
            <tbody>
            <?php /** @var \Milex\WebhookBundle\Entity\Webhook $item */ ?>
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
                                        $permissions['webhook:webhooks:editown'],
                                        $permissions['webhook:webhooks:editother'],
                                        $item->getCreatedBy()
                                    ),
                                    'clone'  => $permissions['webhook:webhooks:create'],
                                    'delete' => $view['security']->hasEntityAccess(
                                        $permissions['webhook:webhooks:deleteown'],
                                        $permissions['webhook:webhooks:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                ],
                                'routeBase' => 'webhook',
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <div>
                            <?php echo $view->render(
                                'MilexCoreBundle:Helper:publishstatus_icon.html.php',
                                ['item' => $item, 'model' => 'webhook']
                            ); ?>
                            <a data-toggle="ajax" href="<?php echo $view['router']->path(
                                'milex_webhook_action',
                                ['objectId' => $item->getId(), 'objectAction' => 'view']
                            ); ?>">
                                <?php echo $item->getName(); ?>
                            </a>
                            <?php if ($description = $item->getDescription()): ?>
                                <div class="text-muted mt-4">
                                    <small><?php echo $description; ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getWebhookUrl(); ?></td>
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
                'menuLinkId' => 'milex_webhook_index',
                'baseUrl'    => $view['router']->path('milex_webhook_index'),
                'sessionVar' => 'milex_webhook',
            ]
        ); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MilexCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
