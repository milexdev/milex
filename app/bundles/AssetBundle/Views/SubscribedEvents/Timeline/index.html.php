<?php

/*
 * @copyright   2016 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<div>
<?php echo $view->render('MilexAssetBundle:Asset:preview.html.php', ['activeAsset' => $event['extra']['asset'], 'assetDownloadUrl' => $view['router']->url(
    'milex_asset_action',
    ['objectAction' => 'preview', 'objectId' => $event['extra']['asset']->getId()]
)]); ?>
</div>