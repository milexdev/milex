<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MilexCoreBundle:Default:slim.html.php');
$view['slots']->set('milexContent', 'social');

$data = json_encode($data);
$js   = <<<JS
function postFormHandler() {
    var opener = window.opener;
    if (opener && typeof opener.postAuthCallback == 'function') {
        opener.postAuthCallback({$data});
    } else {
        Milex.refreshIntegrationForm();
    }
    window.close()
}
JS;

if (!empty($message) && 'success' === $alert):
    $js .= <<<'JS'
    
(function() {
   postFormHandler();
})();
JS;
endif;
?>
<script>
    <?php echo $js; ?>
</script>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $alert; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>
<div class="row">
    <div class="col-sm-12 text-center">
        <a class="btn btn-lg btn-primary" href="javascript:void(0);" onclick="postFormHandler();">
            <?php echo $view['translator']->trans('milex.integration.closewindow'); ?>
        </a>
    </div>
</div>