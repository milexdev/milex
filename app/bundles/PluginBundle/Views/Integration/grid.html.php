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
    $view->extend('MilexPluginBundle:Integration:index.html.php');
}
?>
<?php if (count($items)): ?>
<div class="pa-md bg-auto">
    <div class="row shuffle-integrations native-integrations">
            <?php foreach ($items as $item):
                if (array_key_exists($item['plugin'], $plugins)) {
                    $pluginTitle = $plugins[$item['plugin']]['name'].' - '.$item['display'];
                } else {
                    $pluginTitle = $item['name'].' - '.$item['display'];
                }
                ?>
                <div class="shuffle shuffle-item grid ma-10 pull-left text-center integration plugin<?php echo $item['plugin']; ?> integration-<?php echo $item['name']; ?> <?php if (!$item['enabled']) {
                    echo  'integration-disabled';
                } ?>">
                    <div class="panel ovf-h pa-10">

                        <a href="<?php echo $view['router']->path(($item['isBundle'] ? 'milex_plugin_info' : 'milex_plugin_config'), ['name' => $item['name']]); ?>" data-prevent-dismiss="true" data-toggle="ajaxmodal" data-target="#IntegrationEditModal" data-header="<?php echo $item['display']; ?>"<?php if ($item['isBundle']) {
                    echo ' data-footer="false"';
                } ?>>
                            <p><img style="height: 78px;" class="img img-responsive" src="<?php echo $view['assets']->getUrl($item['icon']); ?>" /></p>
                            <h5 class="mt-20">
                                <span class="ellipsis" data-toggle="tooltip" title="<?php echo $pluginTitle; ?>"><?php echo $item['display']; ?>
                                </span>
                            </h5>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
    </div>
</div>
<?php echo $view->render('MilexCoreBundle:Helper:modal.html.php', [
    'id'            => 'IntegrationEditModal',
    'footerButtons' => true,
]); ?>

<?php else: ?>
    <?php echo $view->render('MilexCoreBundle:Helper:noresults.html.php', [
        'message' => 'milex.integrations.noresults',
        'tip'     => 'milex.integration.noresults.tip',
    ]); ?>
<?php endif; ?>
