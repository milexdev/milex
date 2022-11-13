<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<div class="milexform-row panel<?php if (empty($action['settings']['allowCampaignForm'])) {
    echo ' action-standalone-only';
} ?>" id="milexform_action_<?php echo $id; ?>">
    <?php
    if (!empty($inForm)) {
        echo $view->render('MilexFormBundle:Builder:actions.html.php', [
            'id'         => $id,
            'route'      => 'milex_formaction_action',
            'actionType' => 'action',
            'formId'     => $formId,
        ]);
    }
    ?>
    <a data-toggle="ajaxmodal" data-target="#formComponentModal" href="<?php echo $view['router']->path('milex_formaction_action', ['objectAction' => 'edit', 'objectId' => $id, 'formId' => $formId]); ?>"><span class="action-label"><?php echo $action['name']; ?></span></a>
    <?php if (!empty($action['description'])): ?>
    <span class="action-descr"><?php echo $action['description']; ?></span>
    <?php endif; ?>
</div>