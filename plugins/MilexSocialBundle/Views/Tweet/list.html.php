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
    $view->extend('MilexSocialBundle:Tweet:index.html.php');
}
?>
<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered tweet-list" id="tweetTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#tweetTable',
                        'langVar'         => 'milex.social.tweets',
                        'routeBase'       => 'milex_tweet',
                        'templateButtons' => [
                            'delete' => $view['security']->isGranted('milexSocial:tweet:delete'),
                        ],
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'social.tweet',
                        'orderBy'    => 'e.name',
                        'text'       => 'milex.core.name',
                        'class'      => 'col-tweet-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MilexCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'social.tweet',
                        'orderBy'    => 'e.id',
                        'text'       => 'milex.core.id',
                        'class'      => 'visible-md visible-lg col-asset-id',
                    ]
                );
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $k => $item): ?>
                <tr>
                    <td>
                        <?php
                        echo $view->render(
                            'MilexCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit'   => $view['security']->isGranted('milexSocial:tweet:edit'),
                                    'delete' => $view['security']->isGranted('milexSocial:tweet:delete'),
                                ],
                                'routeBase'  => 'milex_tweet',
                                'langVar'    => 'milex.integration.Twitter',
                                'nameGetter' => 'getName',
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <div>
                            <?php echo $view->render(
                                'MilexCoreBundle:Helper:publishstatus_icon.html.php',
                                [
                                    'item'  => $item,
                                    'model' => 'social.tweet',
                                ]
                            ); ?>
                            <a href="<?php echo $view['router']->path(
                                'milex_tweet_action',
                                ['objectAction' => 'edit', 'objectId' => $item->getId()]
                            ); ?>"
                               data-toggle="ajax">
                                <?php echo $item->getName(); ?>
                            </a>
                        </div>
                        <?php if ($description = $item->getDescription()): ?>
                            <div class="text-muted mt-4">
                                <small><?php echo $description; ?></small>
                            </div>
                        <?php endif; ?>
                    </td>
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
                'menuLinkId' => 'milex_tweet_index',
                'baseUrl'    => $view['router']->path('milex_tweet_index'),
                'sessionVar' => 'social.tweet',
                'routeBase'  => 'tweet',
            ]
        ); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MilexCoreBundle:Helper:noresults.html.php', ['tip' => 'milex.milex.social.tweet.noresults.tip']); ?>
<?php endif; ?>

