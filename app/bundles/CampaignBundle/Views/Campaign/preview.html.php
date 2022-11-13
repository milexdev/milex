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
<div class="builder campaign-builder preview">
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
            /** @var \Milex\CampaignBundle\Entity\Campaign $campaign */
            if (count($campaign->getForms())) {
                $sources = [
                    'sourceType' => 'forms',
                    'campaignId' => $campaignId,
                    'names'      => implode(
                        ', ',
                        array_map(
                            function (Milex\FormBundle\Entity\Form $f) {
                                return $f->getName();
                            },
                            $campaign->getForms()
                                     ->toArray()
                        )
                    ),
                ];
                echo $view->render('MilexCampaignBundle:Source:index.html.php', $sources);
            }

            if (count($campaign->getLists())) {
                $sources = [
                    'sourceType' => 'lists',
                    'campaignId' => $campaignId,
                    'names'      => implode(
                        ', ',
                        array_map(
                            function (Milex\LeadBundle\Entity\LeadList $f) {
                                return $f->getName();
                            },
                            $campaign->getLists()
                                     ->toArray()
                        )
                    ),
                ];
                echo $view->render('MilexCampaignBundle:Source:index.html.php', $sources);
            }

            foreach ($campaignEvents as $event):
                echo $view->render('MilexCampaignBundle:Event:preview.html.php', ['event' => $event, 'campaignId' => $campaignId]);
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
    Milex.campaignBuilderCanvasSettings =
        <?php echo json_encode((object) $canvasSettings, JSON_PRETTY_PRINT); ?>;
    Milex.campaignBuilderCanvasSources =
        <?php echo json_encode((object) $campaignSources, JSON_PRETTY_PRINT); ?>;
    Milex.campaignBuilderCanvasEvents =
        <?php echo json_encode((object) $campaignEvents, JSON_PRETTY_PRINT); ?>;

    Milex.campaignBuilderConnectionRestrictions =
        <?php echo json_encode((object) $eventSettings['connectionRestrictions'], JSON_PRETTY_PRINT); ?>;
</script>
