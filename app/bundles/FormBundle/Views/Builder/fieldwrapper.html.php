<?php

/*
 * @copyright   2016 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Milex\FormBundle\Enum\ConditionalFieldEnum;

if (!isset($inBuilder)) {
    $inBuilder = false;
}

?>

<div class="<?php if (empty($isConditional)): ?>panel<?php else: ?>panel2<?php endif; ?> form-field-wrapper"
     data-sortable-id="milexform_<?php echo $field['id']; ?>">
    <?php if (!empty($isConditional)): ?>
        <?php endif; ?>
        <?php
        echo $view->render(
            'MilexFormBundle:Builder:actions.html.php',
            [
                'id'             => $field['id'],
                'formId'         => $formId,
                'formName'       => '',
                'disallowDelete' => ('button' == $field['type']),
            ]
        );
        ?>
        <div class="row ml-0 mr-0"><?php // wrap in a row to keep bootstrap container classes from affecting builder layout?>
            <?php echo $view->render(
                $template,
                [
                    'field'         => $field,
                    'inForm'        => true,
                    'id'            => $field['id'],
                    'formId'        => $formId,
                    'contactFields' => (isset($contactFields)) ? $contactFields : [],
                    'companyFields' => (isset($companyFields)) ? $companyFields : [],
                    'inBuilder'     => $inBuilder,
                ]
            );
            ?>
        </div>

        <?php if ((isset($field['showWhenValueExists']) && false === $field['showWhenValueExists']) || !empty($field['showAfterXSubmissions'])
            || (isset($field['alwaysDisplay']) && true === $field['alwaysDisplay'])
            || !empty($field['leadField'])
            || !empty($field['conditions'])
        ): ?>
            <div class="panel-footer">
                <?php if (!empty($field['conditions']['expr'])): ?>
                    <span class="inline-spacer">
                    <span style="text-transform: none"><?php echo $view['translator']->trans(
                            'milex.form.field.form.condition.show.on'
                        ); ?></span>
                    <strong><?php echo $formFields[$field['parent']]['label']; ?></strong>
                    <span style="text-transform: none">
                          <?php echo $view['translator']->trans(
                              'milex.core.operator.'.strtolower($field['conditions']['expr'])
                          ); ?>
                        <?php echo $view['translator']->trans(
                            'milex.form.field.form.condition.select.value'
                        ); ?>
                    </span>
                    <strong>
                        <?php if ('in' == $field['conditions']['expr'] && !empty($field['conditions']['any'])): ?>
                            *
                        <?php else: ?>
                        <?php echo implode(', ', $field['conditions']['values']); ?></strong>
                        <?php endif; ?>
                           </strong>
                    </span>
                    <br>
                <?php endif; ?>

                <?php if (!empty($field['leadField'])):
                    $icon = (in_array($field['leadField'], array_keys($companyFields))) ? 'building' : 'user';
                    ?>
                    <i class="fa fa-<?php echo $icon; ?>" aria-hidden="true"></i>
                    <span class="inline-spacer">
            <?php
            if (isset($contactFields[$field['leadField']]['label'])) {
                echo $contactFields[$field['leadField']]['label'];
            } elseif ($companyFields[$field['leadField']]['label']) {
                echo $companyFields[$field['leadField']]['label'];
            } else {
                ucfirst($field['leadField']);
            }
            ?>
        </span>
            <?php endif; ?>
            <?php if (isset($field['alwaysDisplay']) && $field['alwaysDisplay']): ?>
                <i class="fa fa-eye" aria-hidden="true"></i>
                <span class="inline-spacer">
            <?php echo $view['translator']->trans('milex.form.field.form.always_display'); ?>
        </span>
            <?php else: ?>
            <?php if (isset($field['showWhenValueExists']) && false === $field['showWhenValueExists']): ?>
                <i class="fa fa-eye-slash" aria-hidden="true"></i>
                <span class="inline-spacer">
            <?php echo $view['translator']->trans('milex.form.field.hide.if.value'); ?>
        </span>
                <?php endif; ?>
                <?php if (!empty($field['showAfterXSubmissions'])): ?>
                    <i class="fa fa-refresh" aria-hidden="true"></i>
                    <span class="inline-spacer">
            <?php echo $view['translator']->trans(
                'milex.form.field.hide.if.submission.count',
                ['%count%' => (int) $field['showAfterXSubmissions']]
            ); ?>
        </span>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php if (empty($isConditional) && isset($fields) && in_array($field['type'], ConditionalFieldEnum::getConditionalFieldTypes())): ?>
        <div class="row ml-15 mr-0 pb-15">
            <div class="pull-left mt-15">
            <a class="add-new-conditional-field" href="">
                    <i class="fa fa-plus"></i>
                    <?php echo $view['translator']->trans(
                        'milex.form.form.component.fields.conditional'
                    ); ?></a>
            </div>
            <div class="mt-10 col-sm-4 col-xs-12" style="display:none">
                <select class="chosen form-builder-new-component"
                        data-placeholder="<?php echo $view['translator']->trans(
                            'milex.form.form.component.fields'
                        ); ?>">
                    <option value=""></option>
                    <?php foreach ($fields as $conditionalField => $conditionalFieldType): ?>
                        <?php if (!in_array($conditionalFieldType, $viewOnlyFields)): ?>
                            <option data-toggle="ajaxmodal"
                                    data-target="#formComponentModal"
                                    data-href="<?php echo $view['router']->path(
                                        'milex_formfield_action',
                                        [
                                            'objectAction' => 'new',
                                            'type'         => $conditionalFieldType,
                                            'tmpl'         => 'field',
                                            'formId'       => $formId,
                                            'inBuilder'    => $inBuilder,
                                            'parent'       => $field['id'],
                                        ]
                                    ); ?>">
                                <?php echo $conditionalField; ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    <?php endif; ?>

        <?php foreach ($formFields as $field2):
            ?>
            <?php if (!empty($field2['parent']) && $field2['parent'] == $field['id']) : ?>
            <?php if (!empty($field2['isCustom'])):
                $params   = $field2['customParameters'];
                $template = $params['template'];
            else:
                $template = 'MilexFormBundle:Field:'.$field2['type'].'.html.php';
            endif; ?>
            <?php

            echo $view->render(
                'MilexFormBundle:Builder:fieldwrapper.html.php',
                [
                    'isConditional'     => true,
                    'template'          => $template,
                    'field'             => $field2,
                    'viewOnlyFields'    => $viewOnlyFields,
                    'inForm'            => true,
                    'id'                => $field2['id'],
                    'formId'            => $formId,
                    'contactFields'     => $contactFields,
                    'companyFields'     => $companyFields,
                    'inBuilder'         => $inBuilder,
                    'fields'            => $fields,
                    'formFields'        => $formFields,
                ]
            ); ?>
        <?php endif; ?>
        <?php endforeach; ?>
</div>
