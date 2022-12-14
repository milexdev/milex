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
<div class="hide builder campaign-builder live">
    <div class="btns-builder">
        <button type="button" class="btn btn-primary btn-apply-builder" onclick="Milex.saveCampaignFromBuilder();">
            <?php echo $view['translator']->trans('milex.core.form.apply'); ?>
        </button>
        <button type="button" class="btn btn-primary btn-close-campaign-builder"
                onclick="Milex.closeCampaignBuilder();">
            <?php echo $view['translator']->trans('milex.core.close.builder'); ?>
        </button>
    </div>
    <div id="builder-errors" class="alert alert-danger" role="alert" style="display: none;">test</div>
    <div class="builder-content">
        <div id="CampaignCanvas">
            <div id="CampaignEvent_newsource<?php if (!empty($campaignSources)) {
    echo '_hide';
} ?>" class="text-center list-campaign-source list-campaign-leadsource">
                <div class="campaign-event-content">
                    <div>
                        <span class="campaign-event-name ellipsis">
                            <i class="mr-sm fa fa-users"></i> <?php echo $view['translator']->trans('milex.campaign.add_new_source'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php
            foreach ($campaignSources as $source):
                echo $view->render('MilexCampaignBundle:Source:index.html.php', $source);
            endforeach;

            foreach ($campaignEvents as $event):
                $settings = $eventSettings[$event['eventType']][$event['type']];
                $template = isset($settings['template']) ? $settings['template'] : 'MilexCampaignBundle:Event:generic.html.php';

                echo $view->render($template,
                    ['event' => $event, 'campaignId' => $campaignId]);
            endforeach;

            echo $view->render('MilexCampaignBundle:Campaign\Builder:index.html.php',
                [
                    'campaignSources' => $campaignSources,
                    'eventSettings'   => $eventSettings,
                    'campaignId'      => $campaignId,
                ]
            );
            ?>
        </div>
        <div id="EventJumpOverlay"></div>
    </div>
</div>
<!-- dropped coordinates -->
<input type="hidden" value="" id="droppedX"/>
<input type="hidden" value="" id="droppedY"/>
<input type="hidden" value="<?php echo $view->escape($campaignId); ?>" id="campaignId"/>

<?php echo $view->render(
    'MilexCoreBundle:Helper:modal.html.php',
    [
        'id'            => 'CampaignEventModal',
        'header'        => false,
        'footerButtons' => true,
        'dismissible'   => false,
    ]
);

?>
<script>
    /**
     * We typecast to object here so that an empty value will
     * be encoded to {} instead of []. Adding JSON_FORCE_OBJECT
     * is not an option because it does a deep transform to
     * object, whereas typecasting only does the first level.
     */
    Milex.campaignBuilderCanvasSettings =
    <?php echo json_encode((object) $canvasSettings, JSON_PRETTY_PRINT); ?>;
    Milex.campaignBuilderCanvasSources =
    <?php echo json_encode((object) $campaignSources, JSON_PRETTY_PRINT); ?>;
    Milex.campaignBuilderCanvasEvents =
    <?php echo json_encode((object) $campaignEvents, JSON_PRETTY_PRINT); ?>;

    Milex.campaignBuilderConnectionRestrictions =
    <?php echo json_encode((object) $eventSettings['connectionRestrictions'], JSON_PRETTY_PRINT); ?>;
</script>
