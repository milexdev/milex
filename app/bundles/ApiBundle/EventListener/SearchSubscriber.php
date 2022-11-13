<?php

namespace Milex\ApiBundle\EventListener;

use Milex\ApiBundle\Model\ClientModel;
use Milex\CoreBundle\CoreEvents;
use Milex\CoreBundle\Event as MilexEvents;
use Milex\CoreBundle\Helper\TemplatingHelper;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchSubscriber implements EventSubscriberInterface
{
    /**
     * @var ClientModel
     */
    private $apiClientModel;

    /**
     * @var CorePermissions
     */
    private $security;

    /**
     * @var TemplatingHelper
     */
    private $templating;

    public function __construct(ClientModel $apiClientModel, CorePermissions $security, TemplatingHelper $templating)
    {
        $this->apiClientModel = $apiClientModel;
        $this->security       = $security;
        $this->templating     = $templating;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::GLOBAL_SEARCH      => ['onGlobalSearch', 0],
            CoreEvents::BUILD_COMMAND_LIST => ['onBuildCommandList', 0],
        ];
    }

    public function onGlobalSearch(MilexEvents\GlobalSearchEvent $event)
    {
        if ($this->security->isGranted('api:clients:view')) {
            $str = $event->getSearchString();
            if (empty($str)) {
                return;
            }

            $clients = $this->apiClientModel->getEntities(
                [
                    'limit'  => 5,
                    'filter' => $str,
                ]);

            if (count($clients) > 0) {
                $clientResults = [];
                $canEdit       = $this->security->isGranted('api:clients:edit');
                foreach ($clients as $client) {
                    $clientResults[] = $this->templating->getTemplating()->renderResponse(
                        'MilexApiBundle:SubscribedEvents\Search:global.html.php',
                        [
                            'client'  => $client,
                            'canEdit' => $canEdit,
                        ]
                    )->getContent();
                }
                if (count($clients) > 5) {
                    $clientResults[] = $this->templating->getTemplating()->renderResponse(
                        'MilexApiBundle:SubscribedEvents\Search:global.html.php',
                        [
                            'showMore'     => true,
                            'searchString' => $str,
                            'remaining'    => (count($clients) - 5),
                        ]
                    )->getContent();
                }
                $clientResults['count'] = count($clients);
                $event->addResults('milex.api.client.menu.index', $clientResults);
            }
        }
    }

    public function onBuildCommandList(MilexEvents\CommandListEvent $event)
    {
        if ($this->security->isGranted('api:clients:view')) {
            $event->addCommands(
                'milex.api.client.header.index',
                $this->apiClientModel->getCommandList()
            );
        }
    }
}
