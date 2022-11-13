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
$view['slots']->set('milexContent', 'page');
$isExisting = $activePage->getId();

$variantParent = $activePage->getVariantParent();
$subheader     = '';
if ($variantParent) {
    $subheader = '<div><span class="small">'.$view['translator']->trans('milex.core.variant_of', [
                    '%name%'   => $activePage->getTitle(),
                    '%parent%' => $variantParent->getTitle(),
                ]).'</span></div>';
} elseif ($activePage->isVariant(false)) {
    $subheader = '<div><span class="small">'.$view['translator']->trans('milex.page.form.has_variants').'</span></div>';
}

$header = $isExisting ?
    $view['translator']->trans('milex.page.header.edit',
        ['%name%' => $activePage->getTitle()]) :
    $view['translator']->trans('milex.page.header.new');

$view['slots']->set('headerTitle', $header.$subheader);

$template = $form['template']->vars['data'];

$attr                               = $form->vars['attr'];
$attr['data-submit-callback-async'] = 'clearThemeHtmlBeforeSave';

$isCodeMode = ('milex_code_mode' === $activePage->getTemplate());

if (!isset($previewUrl)) {
    $previewUrl = '';
}

?>

<?php echo $view['form']->start($form, ['attr' => $attr]); ?>
<!-- start: box layout -->
<div class="box-layout">
    <!-- container -->
    <div class="col-md-9 bg-white height-auto">
        <div class="row">
            <div class="col-xs-12">
                <!-- tabs controls -->
                <ul class="bg-auto nav nav-tabs pr-md pl-md">
                    <li class="active">
                        <a href="#theme-container" role="tab" data-toggle="tab">
                            <?php echo $view['translator']->trans('milex.core.form.theme'); ?>
                        </a>
                    </li>
                    <li id="advanced-tab" class="hidden">
                        <a href="#advanced-container" role="tab" data-toggle="tab">
                            <?php echo $view['translator']->trans('milex.core.advanced'); ?>
                        </a>
                    </li>
                </ul>

                <!--/ tabs controls -->
                <div class="tab-content pa-md">
                    <div class="tab-pane fade in active bdr-w-0" id="theme-container">
                        <div class="row">
                            <div class="col-md-12">
                                <?php echo $view['form']->row($form['template']); ?>
                            </div>
                        </div>

                        <?php echo $view->render('MilexCoreBundle:Helper:theme_select.html.php', [
                            'type'   => 'page',
                            'themes' => $themes,
                            'active' => $form['template']->vars['value'],
                        ]); ?>
                    </div>

                    <div class="tab-pane fade bdr-w-0" id="advanced-container">
                        <br>
                        <div class="row hidden" id="custom-html-row">
                            <div class="col-md-12">
                                <?php echo $view['form']->label($form['customHtml']); ?>
                                <?php echo $view['form']->widget($form['customHtml']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 bg-white height-auto bdr-l">
        <div class="pr-lg pl-lg pt-md pb-md">
            <?php echo $view['form']->row($form['title']); ?>
            <?php if (!$isVariant): ?>
            <?php echo $view['form']->row($form['alias']); ?>
            <?php else: ?>
            <?php echo $view['form']->row($form['template']); ?>
            <?php endif; ?>
            <?php
            if ($isVariant):
            echo $view['form']->row($form['variantSettings']);

            else:
            echo $view['form']->row($form['category']);
            echo $view['form']->row($form['language']);
            echo $view['form']->row($form['translationParent']);
            endif;

            echo $view['form']->row($form['isPublished']);
            if (($permissions['page:preference_center:editown'] ||
                    $permissions['page:preference_center:editother']) &&
                        !$activePage->isVariant()) {
                echo $view['form']->row($form['isPreferenceCenter']);
            }
            echo $view['form']->row($form['publishUp']);
            echo $view['form']->row($form['publishDown']);

            if (!$isVariant):
            echo $view['form']->row($form['redirectType']);
            echo $view['form']->row($form['redirectUrl']);
            endif;
            echo $view['form']->row($form['noIndex']);
            ?>
            <div class="template-fields<?php echo (!$template) ? ' hide"' : ''; ?>">
                <?php echo $view['form']->row($form['metaDescription']); ?>
            </div>
            <div class="template-fields<?php echo (!$template) ? ' hide"' : ''; ?>">
                <?php echo $view['form']->row($form['headScript']); ?>
            </div>
            <div class="template-fields<?php echo (!$template) ? ' hide"' : ''; ?>">
                <?php echo $view['form']->row($form['footerScript']); ?>
            </div>
            <div class="hide">
                <?php echo $view['form']->rest($form); ?>
            </div>
        </div>
    </div>
</div>
<?php echo $view['form']->end($form); ?>

<?php echo $view->render('MilexCoreBundle:Helper:builder.html.php', [
    'type'          => 'page',
    'isCodeMode'    => $isCodeMode,
    'sectionForm'   => $sectionForm,
    'builderAssets' => $builderAssets,
    'slots'         => $slots,
    'sections'      => $sections,
    'objectId'      => $activePage->getSessionId(),
    'previewUrl'    => $previewUrl,
]); ?>
