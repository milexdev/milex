<?php

declare(strict_types=1);

namespace Milex\CampaignBundle\Tests\Executioner;

use Doctrine\Common\Collections\ArrayCollection;
use Milex\CampaignBundle\Entity\Campaign;
use Milex\CampaignBundle\Entity\Event;
use Milex\CampaignBundle\Executioner\ContactFinder\KickoffContactFinder;
use Milex\CampaignBundle\Executioner\ContactFinder\Limiter\ContactLimiter;
use Milex\CampaignBundle\Executioner\EventExecutioner;
use Milex\CampaignBundle\Executioner\KickoffExecutioner;
use Milex\CampaignBundle\Executioner\Result\Counter;
use Milex\CampaignBundle\Executioner\Scheduler\EventScheduler;
use Milex\CampaignBundle\Executioner\Scheduler\Exception\NotSchedulableException;
use Milex\CoreBundle\Translation\Translator;
use Milex\LeadBundle\Entity\Lead;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\BufferedOutput;

class KickoffExecutionerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject|KickoffContactFinder
     */
    private $kickoffContactFinder;

    /**
     * @var MockObject|Translator
     */
    private $translator;

    /**
     * @var MockObject|EventExecutioner
     */
    private $executioner;

    /**
     * @var MockObject|EventScheduler
     */
    private $scheduler;

    protected function setUp(): void
    {
        $this->kickoffContactFinder = $this->createMock(KickoffContactFinder::class);
        $this->translator           = $this->createMock(Translator::class);
        $this->executioner          = $this->createMock(EventExecutioner::class);
        $this->scheduler            = $this->createMock(EventScheduler::class);
    }

    public function testNoContactsResultInEmptyResults(): void
    {
        $campaign = $this->getMockBuilder(Campaign::class)
            ->getMock();
        $campaign->expects($this->once())
            ->method('getRootEvents')
            ->willReturn(new ArrayCollection());

        $limiter = new ContactLimiter(0, 0, 0, 0);

        $counter = $this->getExecutioner()->execute($campaign, $limiter, new BufferedOutput());

        $this->assertEquals(0, $counter->getTotalEvaluated());
    }

    public function testEventsAreScheduledAndExecuted()
    {
        $this->kickoffContactFinder->expects($this->once())
            ->method('getContactCount')
            ->willReturn(2);

        $this->kickoffContactFinder->expects($this->exactly(3))
            ->method('getContacts')
            ->willReturnOnConsecutiveCalls(
                new ArrayCollection([3 => new Lead()]),
                new ArrayCollection([10 => new Lead()]),
                new ArrayCollection([])
            );

        $event    = new Event();
        $event2   = new Event();
        $campaign = new class() extends Campaign {
            /**
             * @var ArrayCollection<int,Event>
             */
            public ArrayCollection $rootEvents;

            /**
             * @return ArrayCollection<int,Event>
             */
            public function getRootEvents(): ArrayCollection
            {
                return $this->rootEvents;
            }
        };
        $campaign->rootEvents = new ArrayCollection([$event, $event2]);
        $event->setCampaign($campaign);
        $event2->setCampaign($campaign);

        $limiter = new ContactLimiter(0, 0, 0, 0);

        $this->scheduler->expects($this->exactly(4))
            ->method('getExecutionDateTime')
            ->willReturn(new \DateTime());

        $callbackCounter = 0;
        $this->scheduler->expects($this->exactly(4))
            ->method('validateAndScheduleEventForContacts')
            ->willReturnCallback(
                function () use (&$callbackCounter) {
                    ++$callbackCounter;
                    if (in_array($callbackCounter, [3, 4])) {
                        throw new NotSchedulableException();
                    }
                }
            );

        $this->executioner->expects($this->exactly(1))
            ->method('executeEventsForContacts')
            ->withConsecutive(
                [
                    $this->countOf(2),
                    $this->isInstanceOf(ArrayCollection::class),
                    $this->isInstanceOf(Counter::class),
                ],
                [
                    $this->countOf(1),
                        $this->isInstanceOf(ArrayCollection::class),
                        $this->isInstanceOf(Counter::class),
                ]
            );

        $counter = $this->getExecutioner()->execute($campaign, $limiter, new BufferedOutput());

        $this->assertEquals(4, $counter->getTotalEvaluated());
        $this->assertEquals(2, $counter->getTotalScheduled());
    }

    private function getExecutioner(): KickoffExecutioner
    {
        return new KickoffExecutioner(
            new NullLogger(),
            $this->kickoffContactFinder,
            $this->translator,
            $this->executioner,
            $this->scheduler
        );
    }
}
