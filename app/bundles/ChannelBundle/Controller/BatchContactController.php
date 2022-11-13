<?php

namespace Milex\ChannelBundle\Controller;

use Milex\ChannelBundle\Model\ChannelActionModel;
use Milex\ChannelBundle\Model\FrequencyActionModel;
use Milex\CoreBundle\Controller\AbstractFormController;
use Milex\LeadBundle\Form\Type\ContactChannelsType;
use Milex\LeadBundle\Model\LeadModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class BatchContactController extends AbstractFormController
{
    /**
     * @var ChannelActionModel
     */
    private $channelActionModel;

    /**
     * @var FrequencyActionModel
     */
    private $frequencyActionModel;

    /**
     * @var LeadModel
     */
    private $contactModel;

    /**
     * Initialize object props here to simulate constructor
     * and make the future controller refactoring easier.
     */
    public function initialize(FilterControllerEvent $event)
    {
        $this->channelActionModel   = $this->container->get('milex.channel.model.channel.action');
        $this->frequencyActionModel = $this->container->get('milex.channel.model.frequency.action');
        $this->contactModel         = $this->container->get('milex.lead.model.lead');
    }

    /**
     * Execute the batch action.
     *
     * @return JsonResponse
     */
    public function setAction()
    {
        $params = $this->request->get('contact_channels', []);
        $ids    = empty($params['ids']) ? [] : json_decode($params['ids']);

        if ($ids && is_array($ids)) {
            $subscribedChannels = isset($params['subscribed_channels']) ? $params['subscribed_channels'] : [];
            $preferredChannel   = isset($params['preferred_channel']) ? $params['preferred_channel'] : null;

            $this->channelActionModel->update($ids, $subscribedChannels);
            $this->frequencyActionModel->update($ids, $params, $preferredChannel);

            $this->addFlash('milex.lead.batch_leads_affected', [
                '%count%'     => count($ids),
            ]);
        } else {
            $this->addFlash('milex.core.error.ids.missing');
        }

        return new JsonResponse([
            'closeModal' => true,
            'flashes'    => $this->getFlashContent(),
        ]);
    }

    /**
     * View for batch action.
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $route = $this->generateUrl('milex_channel_batch_contact_set');

        return $this->delegateView([
            'viewParameters' => [
                'form'         => $this->createForm(ContactChannelsType::class, [], [
                    'action'        => $route,
                    'channels'      => $this->contactModel->getPreferenceChannels(),
                    'public_view'   => false,
                    'save_button'   => true,
                ])->createView(),
            ],
            'contentTemplate' => 'MilexLeadBundle:Batch:channel.html.php',
            'passthroughVars' => [
                'activeLink'    => '#milex_contact_index',
                'milexContent' => 'leadBatch',
                'route'         => $route,
            ],
        ]);
    }
}
