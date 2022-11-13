<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$scriptSrc = $view['assets']->getUrl('media/js/'.('dev' == $app->getEnvironment() ? 'milex-form-src.js' : 'milex-form.js'), null, null, true);
$scriptSrc = str_replace('/index_dev.php', '', $scriptSrc);
?>

<script type="text/javascript">
    /** This section is only needed once per page if manually copying **/
    if (typeof MilexSDKLoaded == 'undefined') {
        var MilexSDKLoaded = true;
        var head            = document.getElementsByTagName('head')[0];
        var script          = document.createElement('script');
        script.type         = 'text/javascript';
        script.src          = '<?php echo $scriptSrc; ?>';
        script.onload       = function() {
            MilexSDK.onLoad();
        };
        head.appendChild(script);
        var MilexDomain = '<?php echo str_replace('/index_dev.php', '', $view['assets']->getBaseUrl()); ?>';
        var MilexLang   = {
            'submittingMessage': "<?php echo $view['translator']->trans('milex.form.submission.pleasewait'); ?>"
        }
    }else if (typeof MilexSDK != 'undefined') {
        MilexSDK.onLoad();
    }
</script>
