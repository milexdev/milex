<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if (!$app->getRequest()->isXmlHttpRequest()) {
    $view->extend('MilexUserBundle:Security:base.html.php');
} else {
    $view->extend('MilexUserBundle:Security:ajax.html.php');
}
?>

<div class="alert alert-warning"><?php echo $view['translator']->trans('milex.user.user.passwordreset.info'); ?></div>
<?php
echo $view['form']->start($form);
echo $view['form']->row($form['identifier']);
echo $view['form']->widget($form['submit']);
echo $view['form']->end($form);
?>

<div class="mt-sm">
    <a href="<?php echo $view['router']->path('login'); ?>"><?php echo $view['translator']->trans('milex.user.user.passwordreset.back'); ?></a>
</div>
