<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$locale = $app->getRequest()->getLocale();
$js     = <<<js
<script src="//platform.linkedin.com/in.js" type="text/javascript">
    lang: $locale
</script>
js;

$counter     = (!empty($settings['counter'])) ? $settings['counter'] : 'none';
$dataCounter = ('none' != $counter) ? ' data-counter="'.$settings['counter'].'"' : '';
?>
<div class="share-button linkedin-share-button layout-<?php echo $counter; ?>">
<script type="IN/Share"<?php echo $dataCounter; ?>></script>
</div>
<?php echo $js; ?>
