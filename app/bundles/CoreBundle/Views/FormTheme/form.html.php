<?php

/*
 * @copyright   2016 Milex Contributors. All rights reserved
 * @author      Milex, Inc.
 *
 * @link        https://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

if (!isset($entity)) {
    $entity = $form->vars['data'];
}

$extendTemplate = (!empty($useSlim)) ? 'slim' : 'content';
$view->extend('MilexCoreBundle:Default:'.$extendTemplate.'.html.php');
if (isset($milexContent)) {
    $view['slots']->set('milexContent', $milexContent);
}

if (!isset($headerTitle)) {
    if ($entity->getId()) {
        $headerTitle = $view['translator']->hasId($translationBase.'.header.edit')
            ?
            $view['translator']->trans($translationBase.'.header.edit', ['%name%' => $entity->getName()])
            :
            $view['translator']->trans('milex.core.header.edit', ['%name%' => $entity->getName()]);
    } else {
        $headerTitle = $view['translator']->hasId($translationBase.'.header.new')
            ?
            $view['translator']->trans($translationBase.'.header.new')
            :
            $view['translator']->trans('milex.core.header.new');
    }
}
$view['slots']->set('headerTitle', $headerTitle);

$attr = $form->vars['attr'];
if ($view['slots']->has('formAttr')) {
    $attr = array_merge($attr, $view['slots']->get('formAttr'));
}

echo $view['form']->start($form, ['attr' => $attr]);
$view['slots']->output('mainFormContent');
echo $view['form']->end($form);
$view['slots']->output('postFormContent');
?>


