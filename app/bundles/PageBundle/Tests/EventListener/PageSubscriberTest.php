<?php

namespace Milex\PageBundle\Tests\EventListener;

use Milex\CoreBundle\Helper\IpLookupHelper;
use Milex\CoreBundle\Model\AuditLogModel;
use Milex\CoreBundle\Templating\Helper\AssetsHelper;
use Milex\CoreBundle\Translation\Translator;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Entity\LeadRepository;
use Milex\PageBundle\Entity\Hit;
use Milex\PageBundle\Entity\HitRepository;
use Milex\PageBundle\Entity\PageRepository;
use Milex\PageBundle\Entity\RedirectRepository;
use Milex\PageBundle\Event\PageBuilderEvent;
use Milex\PageBundle\EventListener\PageSubscriber;
use Milex\PageBundle\Model\PageModel;
use Milex\QueueBundle\Event\QueueConsumerEvent;
use Milex\QueueBundle\Queue\QueueConsumerResults;
use Milex\QueueBundle\QueueEvents;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;

class PageSubscriberTest extends TestCase
{
    public function testGetTokensWhenCalledReturnsValidTokens()
    {
        $translator       = $this->createMock(Translator::class);
        $pageBuilderEvent = new PageBuilderEvent($translator);
        $pageBuilderEvent->addToken('{token_test}', 'TOKEN VALUE');
        $tokens = $pageBuilderEvent->getTokens();
        $this->assertArrayHasKey('{token_test}', $tokens);
        $this->assertEquals($tokens['{token_test}'], 'TOKEN VALUE');
    }

    public function testOnPageHitWhenCalledAcknowledgesHit()
    {
        $dispatcher = new EventDispatcher();
        $subscriber = $this->getPageSubscriber();

        $dispatcher->addSubscriber($subscriber);

        $payload = $this->getNonEmptyPayload();
        $event   = new QueueConsumerEvent($payload);

        $dispatcher->dispatch(QueueEvents::PAGE_HIT, $event);

        $this->assertEquals($event->getResult(), QueueConsumerResults::ACKNOWLEDGE);
    }

    public function testOnPageHitWhenCalledRejectsBadHit()
    {
        $dispatcher = new EventDispatcher();
        $subscriber = $this->getPageSubscriber();

        $dispatcher->addSubscriber($subscriber);

        $payload = $this->getEmptyPayload();
        $event   = new QueueConsumerEvent($payload);

        $dispatcher->dispatch(QueueEvents::PAGE_HIT, $event);

        $this->assertEquals($event->getResult(), QueueConsumerResults::REJECT);
    }

    /**
     * Get page subscriber with mocked dependencies.
     *
     * @return PageSubscriber
     */
    protected function getPageSubscriber()
    {
        $assetsHelperMock   = $this->createMock(AssetsHelper::class);
        $ipLookupHelperMock = $this->createMock(IpLookupHelper::class);
        $auditLogModelMock  = $this->createMock(AuditLogModel::class);
        $pageModelMock      = $this->createMock(PageModel::class);
        $logger             = $this->createMock(Logger::class);
        $hitRepository      = $this->createMock(HitRepository::class);
        $pageRepository     = $this->createMock(PageRepository::class);
        $redirectRepository = $this->createMock(RedirectRepository::class);
        $contactRepository  = $this->createMock(LeadRepository::class);
        $hitMock            = $this->createMock(Hit::class);
        $leadMock           = $this->createMock(Lead::class);

        $hitRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($hitMock));

        $contactRepository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($leadMock));

        $pageSubscriber = new PageSubscriber(
            $assetsHelperMock,
            $ipLookupHelperMock,
            $auditLogModelMock,
            $pageModelMock,
            $logger,
            $hitRepository,
            $pageRepository,
            $redirectRepository,
            $contactRepository
        );

        return $pageSubscriber;
    }

    /**
     * Get non empty payload, having a Request and non-null entity IDs.
     *
     * @return array
     */
    protected function getNonEmptyPayload()
    {
        $requestMock = $this->createMock(Request::class);

        return [
            'request' => $requestMock,
            'isNew'   => true,
            'hitId'   => 123,
            'pageId'  => 456,
            'leadId'  => 789,
        ];
    }

    /**
     * Get empty payload with all null entity IDs.
     *
     * @return array
     */
    protected function getEmptyPayload()
    {
        return array_fill_keys(['request', 'isNew', 'hitId', 'pageId', 'leadId'], null);
    }
}
