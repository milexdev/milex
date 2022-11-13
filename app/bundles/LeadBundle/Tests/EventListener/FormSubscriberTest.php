<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Tests\EventListener;

use Milex\CoreBundle\Entity\IpAddress;
use Milex\CoreBundle\Helper\IpLookupHelper;
use Milex\EmailBundle\Model\EmailModel;
use Milex\FormBundle\Entity\Action;
use Milex\FormBundle\Entity\Form;
use Milex\FormBundle\Entity\Submission;
use Milex\FormBundle\Event\SubmissionEvent;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\EventListener\FormSubscriber;
use Milex\LeadBundle\Model\LeadModel;
use Milex\LeadBundle\Tracker\ContactTracker;
use Symfony\Component\HttpFoundation\Request;

class FormSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EmailModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $emailModel;

    /**
     * @var LeadModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $leadModel;

    /**
     * @var ContactTracker|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contactTracker;

    /**
     * @var IpLookupHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $ipLookupHelper;

    protected function setUp(): void
    {
        $this->emailModel     = $this->createMock(EmailModel::class);
        $this->leadModel      = $this->createMock(LeadModel::class);
        $this->contactTracker = $this->createMock(ContactTracker::class);
        $this->ipLookupHelper = $this->createMock(IpLookupHelper::class);
    }

    public function testOnFormSubmitActionChangePoints()
    {
        $this->contactTracker->method('getContact')->willReturn(new Lead());

        $this->ipLookupHelper->method('getIpAddress')->willReturn(new IpAddress());

        $formSubscriber = new FormSubscriber(
            $this->emailModel,
            $this->leadModel,
            $this->contactTracker,
            $this->ipLookupHelper
        );

        $submission = new Submission();
        $submission->setForm(new Form());
        $submission->setLead(new Lead());

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $submissionEvent = new SubmissionEvent($submission, [], [], $request);

        $action = new Action();
        $action->setType('lead.pointschange');
        $action->setProperties(['points' => 1, 'operator' => 'plus']);
        $submissionEvent->setAction($action);

        $formSubscriber->onFormSubmitActionChangePoints($submissionEvent);

        $this->assertEquals(1, $submissionEvent->getSubmission()->getLead()->getPoints());
    }
}
