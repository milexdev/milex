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
$view['slots']->set('milexContent', 'form');

$header = ($activeForm->getId())
    ?
    $view['translator']->trans(
        'milex.form.form.header.edit',
        ['%name%' => $view['translator']->trans($activeForm->getName())]
    )
    :
    $view['translator']->trans('milex.form.form.header.new');
$view['slots']->set('headerTitle', $header);

$formId = $form['sessionId']->vars['data'];

if (!isset($inBuilder)) {
    $inBuilder = false;
}

$fieldsTabError = false;
if ($view['form']->errors(
    $form['progressiveProfilingLimit']
)) {
    $fieldsTabError = true;
}
?>
<?php echo $view['form']->start($form); ?>
<div class="box-layout">
    <div class="col-md-9 height-auto bg-white">
        <div class="row">
            <div class="col-xs-12">
                <!-- tabs controls -->
                <ul class="bg-auto nav nav-tabs pr-md pl-md">
                    <li class="active"><a href="#details-container" role="tab" data-toggle="tab"><?php echo $view['translator']->trans(
                                'milex.core.details'
                            ); ?></a></li>
                    <li id="fields-tab" class="text-danger"><a class="<?php if ($fieldsTabError) {
                                echo 'text-danger';
                            } ?>" href="#fields-container" role="tab" data-toggle="tab"><?php echo $view['translator']->trans(
                                'milex.form.tab.fields'
                            ); ?>
                            <?php if ($fieldsTabError): ?>
                                <i class="fa fa-warning"></i>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li id="actions-tab"><a href="#actions-container" role="tab" data-toggle="tab"><?php echo $view['translator']->trans(
                                'milex.form.tab.actions'
                            ); ?></a></li>
                </ul>
                <!--/ tabs controls -->
                <div class="tab-content pa-md">
                    <div class="tab-pane fade in active bdr-w-0" id="details-container">
                        <div class="row">
                            <div class="col-md-6">
                                <?php
                                echo $view['form']->row($form['name']);
                                echo $view['form']->row($form['formAttributes']);
                                echo $view['form']->row($form['description']);
                                ?>
                            </div>
                            <div class="col-md-6">
                                <?php
                                echo $view['form']->row($form['postAction']);
                                echo $view['form']->row($form['postActionProperty']);
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade bdr-w-0" id="fields-container">
                        <?php echo $view->render('MilexFormBundle:Builder:style.html.php'); ?>
                        <div id="milexforms_fields">
                            <div class="row">
                                <div class="available-fields mb-md col-sm-4">
                                    <select class="chosen form-builder-new-component" data-placeholder="<?php echo $view['translator']->trans('milex.form.form.component.fields'); ?>">
                                        <option value=""></option>
                                        <?php foreach ($fields as $field => $fieldType): ?>

                                            <option data-toggle="ajaxmodal"
                                                    data-target="#formComponentModal"
                                                    data-href="<?php echo $view['router']->path(
                                                        'milex_formfield_action',
                                                        [
                                                            'objectAction' => 'new',
                                                            'type'         => $fieldType,
                                                            'tmpl'         => 'field',
                                                            'formId'       => $formId,
                                                            'inBuilder'    => $inBuilder,
                                                        ]
                                                    ); ?>">
                                                <?php echo $field; ?>
                                            </option>
                                        <?php endforeach; ?>

                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 mb-10">
                                    <?php echo $view['form']->label(
                                        $form['progressiveProfilingLimit']
                                    ); ?>
                                    <div class="ml-5 mr-5" style="display:inline-block;">
                                        <?php echo $view['form']->widget(
                                            $form['progressiveProfilingLimit']
                                        ); ?>
                                    </div>
                                    <div class="has-error" style="display:inline-block;">
                                        <?php echo $view['form']->errors(
                                            $form['progressiveProfilingLimit']
                                        ); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="drop-here">
                            <?php foreach ($formFields as $field): ?>
                            <?php if (!is_null($field['parent'])) {
                                            continue;
                                        }
                                ?>
                                <?php if (!in_array($field['id'], $deletedFields)) : ?>
                                    <?php if (!empty($field['isCustom'])):
                                        $params   = $field['customParameters'];
                                        $template = $params['template'];
                                    else:
                                        $template = 'MilexFormBundle:Field:'.$field['type'].'.html.php';
                                    endif; ?>
                                    <?php

                                    echo $view->render(
                                        'MilexFormBundle:Builder:fieldwrapper.html.php',
                                        [
                                            'template'       => $template,
                                            'field'          => $field,
                                            'viewOnlyFields' => $viewOnlyFields,
                                            'inForm'         => true,
                                            'id'             => $field['id'],
                                            'formId'         => $formId,
                                            'formName'       => $activeForm->generateFormName(),
                                            'contactFields'  => $contactFields,
                                            'companyFields'  => $companyFields,
                                            'inBuilder'      => $inBuilder,
                                            'fields'         => $fields,
                                            'formFields'     => $formFields,
                                        ]
                                    ); ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            </div>
                            <?php if (!count($formFields)): ?>
                            <div class="alert alert-info" id="form-field-placeholder">
                                <p><?php echo $view['translator']->trans('milex.form.form.addfield'); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="tab-pane fade bdr-w-0" id="actions-container">
                        <div id="milexforms_actions">
                            <div class="row">
                                <div class="available-actions mb-md col-sm-4">
                                    <select class="chosen form-builder-new-component" data-placeholder="<?php echo $view['translator']->trans('milex.form.form.component.submitactions'); ?>">
                                        <option value=""></option>
                                        <?php foreach ($actions as $group => $groupActions): ?>
                                            <?php
                                                $campaignActionFound = false;
                                                $actionOptions       = '';
                                                foreach ($groupActions as $k => $e):
                                                    $actionOptions .= $view->render(
                                                        'MilexFormBundle:Action:option.html.php',
                                                        [
                                                            'action'       => $e,
                                                            'type'         => $k,
                                                            'isStandalone' => $activeForm->isStandalone(),
                                                            'formId'       => $form['sessionId']->vars['data'],
                                                        ]
                                                    )."\n\n";
                                                if (!empty($e['allowCampaignForm'])) {
                                                    $campaignActionFound = true;
                                                }
                                                endforeach;
                                            $class = (empty($campaignActionFound)) ? ' action-standalone-only' : '';
                                            if (!$campaignActionFound && !$activeForm->isStandalone()) {
                                                $class .= ' hide';
                                            }
                                            ?>
                                            <optgroup class=<?php echo $class; ?> label="<?php echo $view['translator']->trans($group); ?>"></optgroup>
                                            <?php echo $actionOptions; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="drop-here">
                            <?php foreach ($formActions as $action): ?>
                                <?php if (!in_array($action['id'], $deletedActions)) : ?>
                                    <?php $template = (isset($actionSettings[$action['type']]['template']))
                                        ? $actionSettings[$action['type']]['template']
                                        :
                                        'MilexFormBundle:Action:generic.html.php';
                                    $action['settings'] = $actionSettings[$action['type']];
                                    echo $view->render(
                                        $template,
                                        [
                                            'action' => $action,
                                            'inForm' => true,
                                            'id'     => $action['id'],
                                            'formId' => $formId,
                                        ]
                                    ); ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            </div>
                            <?php if (!count($formActions)): ?>
                            <div class="alert alert-info" id="form-action-placeholder">
                                <p><?php echo $view['translator']->trans('milex.form.form.addaction'); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 bg-white height-auto bdr-l">
        <div class="pr-lg pl-lg pt-md pb-md">
            <?php
            echo $view['form']->row($form['category']);
            echo $view['form']->row($form['isPublished']);
            echo $view['form']->row($form['publishUp']);
            echo $view['form']->row($form['publishDown']);
            echo $view['form']->row($form['noIndex']);
            echo $view['form']->row($form['inKioskMode']);
            echo $view['form']->row($form['renderStyle']);
            echo $view['form']->row($form['template']);
            ?>
        </div>
    </div>
</div>
<?php

echo $view['form']->end($form);

if (null === $activeForm->getFormType() || !empty($forceTypeSelection)):
    echo $view->render(
        'MilexCoreBundle:Helper:form_selecttype.html.php',
        [
            'item'       => $activeForm,
            'milexLang' => [
                'newStandaloneForm' => 'milex.form.type.standalone.header',
                'newCampaignForm'   => 'milex.form.type.campaign.header',
            ],
            'typePrefix'         => 'form',
            'cancelUrl'          => 'milex_form_index',
            'header'             => 'milex.form.type.header',
            'typeOneHeader'      => 'milex.form.type.campaign.header',
            'typeOneIconClass'   => 'fa-cubes',
            'typeOneDescription' => 'milex.form.type.campaign.description',
            'typeOneOnClick'     => "Milex.selectFormType('campaign');",
            'typeTwoHeader'      => 'milex.form.type.standalone.header',
            'typeTwoIconClass'   => 'fa-list',
            'typeTwoDescription' => 'milex.form.type.standalone.description',
            'typeTwoOnClick'     => "Milex.selectFormType('standalone');",
        ]
    );
endif;

$view['slots']->append(
    'modal',
    $view->render(
        'MilexCoreBundle:Helper:modal.html.php',
        [
            'id'            => 'formComponentModal',
            'header'        => false,
            'footerButtons' => true,
        ]
    )
);
?>
