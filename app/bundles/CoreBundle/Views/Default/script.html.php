<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$milexContent = $view['slots']->get(
    'milexContent',
    isset($milexTemplateVars['milexContent']) ? $milexTemplateVars['milexContent'] : ''
);
?>

<script>
    var milexBasePath    = '<?php echo $app->getRequest()->getBasePath(); ?>';
    var milexBaseUrl     = '<?php echo $view['router']->path('milex_base_index'); ?>';
    var milexAjaxUrl     = '<?php echo $view['router']->path('milex_core_ajax'); ?>';
    var milexAjaxCsrf    = '<?php echo $view['security']->getCsrfToken('milex_ajax_post'); ?>';
    var milexImagesPath  = '<?php echo $view['assets']->getImagesPath(); ?>';
    var milexAssetPrefix = '<?php echo $view['assets']->getAssetPrefix(true); ?>';
    var milexContent     = '<?php echo $milexContent; ?>';
    var milexEnv         = '<?php echo $app->getEnvironment(); ?>';
    var milexLang        = <?php echo $view['translator']->getJsLang(); ?>;
    var milexLocale      = '<?php echo $app->getRequest()->getLocale(); ?>';
    var milexEditorFonts = <?php echo json_encode($view['config']->get('editor_fonts')); ?>;
</script>
<?php $view['assets']->outputSystemScripts(true); ?>
