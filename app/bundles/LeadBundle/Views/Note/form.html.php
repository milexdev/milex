<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view['slots']->set('milexContent', 'leadnote');
$userId = $form->vars['data']->getId();
if (!empty($userId)) {
    $header = $view['translator']->trans('milex.lead.note.header.edit');
} else {
    $header = $view['translator']->trans('milex.lead.note.header.new');
}
?>
<?php echo $view['form']->start($form); ?>
<?php echo $view['form']->row($form['text']); ?>

<div class="row">
    <div class="col-xs-6">
        <?php echo $view['form']->widget($form['type']); ?>
    </div>
    <div class="col-xs-6">
        <?php echo $view['form']->widget($form['dateTime']); ?>
    </div>
</div>

<?php echo $view['form']->row($form['buttons']); ?>
<?php echo $view['form']->end($form); ?>