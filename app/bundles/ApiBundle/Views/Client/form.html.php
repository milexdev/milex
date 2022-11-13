<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MilexCoreBundle:Default:content.html.php');
$view['slots']->set('milexContent', 'client');
$id = $form->vars['data']->getId();
if (!empty($id)) {
    $name   = $form->vars['data']->getName();
    $header = $view['translator']->trans('milex.api.client.header.edit', ['%name%' => $name]);
} else {
    $header = $view['translator']->trans('milex.api.client.header.new');
}
$view['slots']->set('headerTitle', $header);
?>

<div class="row">
    <div class="pa-md">
        <div class="col-md-6">
            <?php echo $view['form']->form($form); ?>
        </div>
    </div>
</div>