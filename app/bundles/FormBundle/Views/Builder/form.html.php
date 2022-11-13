<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
/** @var \Milex\FormBundle\Entity\Form $form */
$formName = '_'.$form->generateFormName().(isset($suffix) ? $suffix : '');
if (!isset($fields)) {
    $fields = $form->getFields();
}
$pageCount = 1;

if (!isset($inBuilder)) {
    $inBuilder = false;
}

if (!isset($action)) {
    $action = $view['router']->url('milex_form_postresults', ['formId' => $form->getId()]);
}

if (!isset($theme)) {
    $theme = '';
}

if (!isset($contactFields)) {
    $contactFields = $companyFields = [];
}

if (!isset($style)) {
    $style = '';
}

if (!isset($isAjax)) {
    $isAjax = true;
}

if (!isset($submissions)) {
    $submissions = null;
}

if (!isset($lead)) {
    $lead = null;
}
?>

<?php echo $style; ?>
<style type="text/css" scoped>
    .milexform-field-hidden { display:none }
</style>

<div id="milexform_wrapper<?php echo $formName; ?>" class="milexform_wrapper">
    <form autocomplete="false" role="form" method="post" action="<?php echo  $action; ?>" id="milexform<?php echo $formName; ?>" <?php if ($isAjax): ?> data-milex-form="<?php echo ltrim($formName, '_'); ?>"<?php endif; ?> enctype="multipart/form-data" <?php echo $form->getFormAttributes(); ?>>
        <div class="milexform-error" id="milexform<?php echo $formName; ?>_error"></div>
        <div class="milexform-message" id="milexform<?php echo $formName; ?>_message"></div>
        <div class="milexform-innerform">
            <?php
            $displayManager = new \Milex\FormBundle\ProgressiveProfiling\DisplayManager(
                $form,
                !empty($viewOnlyFields) ? $viewOnlyFields : []
            );
            /** @var \Milex\FormBundle\Entity\Field $f */
            foreach ($fields as $fieldId => $f):
                if (isset($formPages['open'][$fieldId])):
                    // Start a new page
                    $lastFieldAttribute = ($lastFormPage === $fieldId) ? ' data-milex-form-pagebreak-lastpage="true"' : '';
                    echo "\n          <div class=\"milexform-page-wrapper milexform-page-$pageCount\" data-milex-form-page=\"$pageCount\"$lastFieldAttribute>\n";
                endif;

                if (!$f->getParent() && $f->showForContact($submissions, $lead, $form, $displayManager)):
                    if ($f->isCustom()):
                        if (!isset($fieldSettings[$f->getType()])):
                            continue;
                        endif;
                        $params = $fieldSettings[$f->getType()];
                        $f->setCustomParameters($params);

                        $template = $params['template'];
                    else:
                        if (!$f->isAlwaysDisplay() && !$f->getShowWhenValueExists() && $f->getLeadField() && $f->getIsAutoFill() && $lead && !empty($lead->getFieldValue($f->getLeadField()))) {
                            $f->setType('hidden');
                        } else {
                            $displayManager->increaseDisplayedFields($f);
                        }
                        $template = 'MilexFormBundle:Field:'.$f->getType().'.html.php';
                    endif;

                    echo $view->render(
                        $theme.$template,
                        [
                            'field'         => $f->convertToArray(),
                            'id'            => $f->getAlias(),
                            'formName'      => $formName,
                            'fieldPage'     => ($pageCount - 1), // current page,
                            'contactFields' => $contactFields,
                            'companyFields' => $companyFields,
                            'inBuilder'     => $inBuilder,
                            'fields'        => $fields,
                        ]
                    );
                endif;
                $parentField = $f;
                foreach ($fields as $fieldId2 => $f):
                    if ('hidden' !== $parentField->getType() && $f->getParent() == $parentField->getId()):
                    if ($f->isCustom()):
                        if (!isset($fieldSettings[$f->getType()])):
                            continue;
                        endif;
                        $params = $fieldSettings[$f->getType()];
                        $f->setCustomParameters($params);

                        $template = $params['template'];
                    else:
                        $template = 'MilexFormBundle:Field:'.$f->getType().'.html.php';
                    endif;

                    echo $view->render(
                        $theme.$template,
                        [
                            'field'         => $f->convertToArray(),
                            'id'            => $f->getAlias(),
                            'formName'      => $formName,
                            'fieldPage'     => ($pageCount - 1), // current page,
                            'contactFields' => $contactFields,
                            'companyFields' => $companyFields,
                            'inBuilder'     => $inBuilder,
                            'fields'        => $fields,
                        ]
                    );
                    endif;
                endforeach;

                if (isset($formPages) && isset($formPages['close'][$fieldId])):
                    // Close the page
                    echo "\n            </div>\n";
                    ++$pageCount;
                endif;

            endforeach;
            ?>
        </div>

        <input type="hidden" name="milexform[formId]" id="milexform<?php echo $formName; ?>_id" value="<?php echo $view->escape($form->getId()); ?>"/>
        <input type="hidden" name="milexform[return]" id="milexform<?php echo $formName; ?>_return" value=""/>
        <input type="hidden" name="milexform[formName]" id="milexform<?php echo $formName; ?>_name" value="<?php echo $view->escape(ltrim($formName, '_')); ?>"/>

        <?php echo (isset($formExtra)) ? $formExtra : ''; ?>
</form>
</div>
