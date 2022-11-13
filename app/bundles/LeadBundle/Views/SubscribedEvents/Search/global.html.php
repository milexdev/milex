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
<?php if (!empty($showMore)): ?>
    <a href="<?php echo $view['router']->url('milex_contact_index', ['search' => $searchString]); ?>" data-toggle="ajax">
        <span><?php echo $view['translator']->trans('milex.core.search.more', ['%count%' => $remaining]); ?></span>
    </a>
<?php else: ?>
    <?php $fields = $lead->getFields(); ?>
    <span class="pull-left pr-xs pt-xs" style="width:36px">
        <span class="img-wrapper img-rounded"><img src="<?php echo $view['gravatar']->getImage($fields['core']['email']['value'], '100'); ?>" /></span>
    </span>
    <a href="<?php echo $view['router']->url('milex_contact_action', ['objectAction' => 'view', 'objectId' => $lead->getId()]); ?>" data-toggle="ajax">
        <span><?php echo $lead->getPrimaryIdentifier(true); ?></span>
        <?php
        $color = $lead->getColor();
        $style = !empty($color) ? ' style="background-color: '.$color.';"' : '';
        ?>
        <span class="label label-default pull-right"<?php echo $style; ?> data-toggle="tooltip" data-placement="left" title="<?php echo $view['translator']->trans('milex.lead.lead.pointscount'); ?>"><?php echo $lead->getPoints(); ?></span>
    </a>
    <div class="clearfix"></div>
<?php endif; ?>