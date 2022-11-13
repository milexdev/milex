<?php

namespace Milex\PageBundle\Tests;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\Helper\CookieHelper;
use Milex\CoreBundle\Helper\CoreParametersHelper;
use Milex\CoreBundle\Helper\IpLookupHelper;
use Milex\CoreBundle\Helper\UrlHelper;
use Milex\CoreBundle\Helper\UserHelper;
use Milex\CoreBundle\Translation\Translator;
use Milex\LeadBundle\Model\CompanyModel;
use Milex\LeadBundle\Model\FieldModel;
use Milex\LeadBundle\Model\LeadModel;
use Milex\LeadBundle\Tracker\ContactTracker;
use Milex\LeadBundle\Tracker\DeviceTracker;
use Milex\PageBundle\Entity\HitRepository;
use Milex\PageBundle\Entity\PageRepository;
use Milex\PageBundle\Model\PageModel;
use Milex\PageBundle\Model\RedirectModel;
use Milex\PageBundle\Model\TrackableModel;
use Milex\QueueBundle\Queue\QueueService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PageTestAbstract extends WebTestCase
{
    protected static $mockId   = 123;
    protected static $mockName = 'Mock test name';
    protected $mockTrackingId;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->mockTrackingId = hash('sha1', uniqid(mt_rand(), true));
    }

    /**
     * @return PageModel
     */
    protected function getPageModel($transliterationEnabled = true)
    {
        $cookieHelper = $this
            ->getMockBuilder(CookieHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $router = self::$container->get('router');

        $ipLookupHelper = $this
            ->getMockBuilder(IpLookupHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $leadModel = $this
            ->getMockBuilder(LeadModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $leadFieldModel = $this
            ->getMockBuilder(FieldModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $redirectModel = $this->getRedirectModel();

        $companyModel = $this
            ->getMockBuilder(CompanyModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $trackableModel = $this
            ->getMockBuilder(TrackableModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher = $this
            ->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this
            ->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pageRepository = $this
            ->getMockBuilder(PageRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $coreParametersHelper = $this
            ->getMockBuilder(CoreParametersHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $hitRepository = $this->createMock(HitRepository::class);
        $userHelper    = $this->createMock(UserHelper::class);

        $queueService = $this
            ->getMockBuilder(QueueService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contactTracker = $this->createMock(ContactTracker::class);

        $contactTracker->expects($this
            ->any())
            ->method('getContact')
            ->willReturn($this
                ->returnValue(['id' => self::$mockId, 'name' => self::$mockName])
            );

        $queueService->expects($this
            ->any())
            ->method('isQueueEnabled')
            ->will(
                $this->returnValue(false)
            );

        $entityManager->expects($this
            ->any())
            ->method('getRepository')
            ->will(
                $this->returnValueMap(
                    [
                        ['MilexPageBundle:Page', $pageRepository],
                        ['MilexPageBundle:Hit', $hitRepository],
                    ]
                )
            );

        $coreParametersHelper->expects($this->any())
                ->method('get')
                ->with('transliterate_page_title')
                ->willReturn($transliterationEnabled);

        $deviceTrackerMock = $this->createMock(DeviceTracker::class);

        $pageModel = new PageModel(
            $cookieHelper,
            $ipLookupHelper,
            $leadModel,
            $leadFieldModel,
            $redirectModel,
            $trackableModel,
            $queueService,
            $companyModel,
            $deviceTrackerMock,
            $contactTracker,
            $coreParametersHelper
        );

        $pageModel->setDispatcher($dispatcher);
        $pageModel->setTranslator($translator);
        $pageModel->setEntityManager($entityManager);
        $pageModel->setRouter($router);
        $pageModel->setUserHelper($userHelper);

        return $pageModel;
    }

    /**
     * @return RedirectModel
     */
    protected function getRedirectModel()
    {
        $urlHelper = $this
            ->getMockBuilder(UrlHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockRedirectModel = $this->getMockBuilder('Milex\PageBundle\Model\RedirectModel')
            ->setConstructorArgs([$urlHelper])
            ->setMethods(['createRedirectEntity', 'generateRedirectUrl'])
            ->getMock();

        $mockRedirect = $this->getMockBuilder('Milex\PageBundle\Entity\Redirect')
            ->getMock();

        $mockRedirectModel->expects($this->any())
            ->method('createRedirectEntity')
            ->willReturn($mockRedirect);

        $mockRedirectModel->expects($this->any())
            ->method('generateRedirectUrl')
            ->willReturn('http://some-url.com');

        return $mockRedirectModel;
    }
}
