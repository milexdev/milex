<?php

namespace Milex\PageBundle\EventListener;

use Milex\CoreBundle\CoreEvents;
use Milex\CoreBundle\Event as MilexEvents;
use Milex\CoreBundle\Helper\TemplatingHelper;
use Milex\CoreBundle\Helper\UserHelper;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\PageBundle\Model\PageModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchSubscriber implements EventSubscriberInterface
{
    /**
     * @var UserHelper
     */
    private $userHelper;

    /**
     * @var PageModel
     */
    private $pageModel;

    /**
     * @var CorePermissions
     */
    private $security;

    /**
     * @var TemplatingHelper
     */
    private $templating;

    public function __construct(
        UserHelper $userHelper,
        PageModel $pageModel,
        CorePermissions $security,
        TemplatingHelper $templating
    ) {
        $this->userHelper = $userHelper;
        $this->pageModel  = $pageModel;
        $this->security   = $security;
        $this->templating = $templating;
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
        $str = $event->getSearchString();
        if (empty($str)) {
            return;
        }

        $filter = ['string' => $str, 'force' => []];

        $permissions = $this->security->isGranted(
            ['page:pages:viewown', 'page:pages:viewother'],
            'RETURN_ARRAY'
        );
        if ($permissions['page:pages:viewown'] || $permissions['page:pages:viewother']) {
            if (!$permissions['page:pages:viewother']) {
                $filter['force'][] = [
                    'column' => 'IDENTITY(p.createdBy)',
                    'expr'   => 'eq',
                    'value'  => $this->userHelper->getUser()->getId(),
                ];
            }

            $pages = $this->pageModel->getEntities(
                [
                    'limit'  => 5,
                    'filter' => $filter,
                ]);

            if (count($pages) > 0) {
                $pageResults = [];

                foreach ($pages as $page) {
                    $pageResults[] = $this->templating->getTemplating()->renderResponse(
                        'MilexPageBundle:SubscribedEvents\Search:global.html.php',
                        ['page' => $page]
                    )->getContent();
                }
                if (count($pages) > 5) {
                    $pageResults[] = $this->templating->getTemplating()->renderResponse(
                        'MilexPageBundle:SubscribedEvents\Search:global.html.php',
                        [
                            'showMore'     => true,
                            'searchString' => $str,
                            'remaining'    => (count($pages) - 5),
                        ]
                    )->getContent();
                }
                $pageResults['count'] = count($pages);
                $event->addResults('milex.page.pages', $pageResults);
            }
        }
    }

    public function onBuildCommandList(MilexEvents\CommandListEvent $event)
    {
        if ($this->security->isGranted(['page:pages:viewown', 'page:pages:viewother'], 'MATCH_ONE')) {
            $event->addCommands(
                'milex.page.pages',
                $this->pageModel->getCommandList()
            );
        }
    }
}
