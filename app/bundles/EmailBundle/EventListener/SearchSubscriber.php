<?php

namespace Milex\EmailBundle\EventListener;

use Milex\CoreBundle\CoreEvents;
use Milex\CoreBundle\Event as MilexEvents;
use Milex\CoreBundle\Helper\TemplatingHelper;
use Milex\CoreBundle\Helper\UserHelper;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\EmailBundle\Model\EmailModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchSubscriber implements EventSubscriberInterface
{
    /**
     * @var EmailModel
     */
    private $emailModel;

    /**
     * @var UserHelper
     */
    private $userHelper;

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
        EmailModel $emailModel,
        CorePermissions $security,
        TemplatingHelper $templating
    ) {
        $this->userHelper = $userHelper;
        $this->emailModel = $emailModel;
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

        $filter      = ['string' => $str, 'force' => []];
        $permissions = $this->security->isGranted(
            ['email:emails:viewown', 'email:emails:viewother'],
            'RETURN_ARRAY'
        );
        if ($permissions['email:emails:viewown'] || $permissions['email:emails:viewother']) {
            if (!$permissions['email:emails:viewother']) {
                $filter['force'][] = [
                    'column' => 'IDENTITY(e.createdBy)',
                    'expr'   => 'eq',
                    'value'  => $this->userHelper->getUser()->getId(),
                ];
            }

            $emails = $this->emailModel->getEntities(
                [
                    'limit'  => 5,
                    'filter' => $filter,
                ]);

            if (count($emails) > 0) {
                $emailResults = [];

                foreach ($emails as $email) {
                    $emailResults[] = $this->templating->getTemplating()->renderResponse(
                        'MilexEmailBundle:SubscribedEvents\Search:global.html.php',
                        ['email' => $email]
                    )->getContent();
                }
                if (count($emails) > 5) {
                    $emailResults[] = $this->templating->getTemplating()->renderResponse(
                        'MilexEmailBundle:SubscribedEvents\Search:global.html.php',
                        [
                            'showMore'     => true,
                            'searchString' => $str,
                            'remaining'    => (count($emails) - 5),
                        ]
                    )->getContent();
                }
                $emailResults['count'] = count($emails);
                $event->addResults('milex.email.emails', $emailResults);
            }
        }
    }

    public function onBuildCommandList(MilexEvents\CommandListEvent $event)
    {
        if ($this->security->isGranted(['email:emails:viewown', 'email:emails:viewother'], 'MATCH_ONE')) {
            $event->addCommands(
                'milex.email.emails',
                $this->emailModel->getCommandList()
            );
        }
    }
}
