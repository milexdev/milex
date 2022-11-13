<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$leadId   = $lead->getId();
$leadName = $lead->getPrimaryIdentifier();
?>
<ul class="list-group">
    <?php foreach ($campaigns as $c):
        $switch  = $c['inCampaign'] ? 'fa-toggle-on' : 'fa-toggle-off';
        $bgClass = $c['inCampaign'] ? 'text-success' : 'text-danger';
    ?>
    <li class="list-group-item">
        <i class="fa fa-lg fa-fw <?php echo $switch.' '.$bgClass; ?>" id="leadCampaignToggle<?php echo $c['id']; ?>" onclick="Milex.toggleLeadCampaign('leadCampaignToggle<?php echo $c['id']; ?>', <?php echo $leadId; ?>, <?php echo $c['id']; ?>);"></i>
        <a  data-dismiss="modal" class="pull-right" href="<?php echo $view['router']->url('milex_campaign_action', ['objectAction' => 'view', 'objectId' => $c['id']]); ?>" data-toggle="ajax"><span class="label label-primary"><?php echo $view['translator']->trans('milex.core.id'); ?>: <?php echo $c['id']; ?></span></a>
        <span> <?php echo $c['name']; ?></span>
    </li>
    <?php endforeach; ?>
</ul>