<?php

namespace Milex\LeadBundle\Tests\EventListener;

use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Event\DoNotContactAddEvent;
use Milex\LeadBundle\Event\DoNotContactRemoveEvent;
use Milex\LeadBundle\EventListener\DoNotContactSubscriber;
use Milex\LeadBundle\Model\DoNotContact;

class DoNotContactSubscriberTest extends \PHPUnit\Framework\TestCase
{
    private $doNotContactSubscriber;

    private $doNotContact;

    protected function setUp(): void
    {
        $this->doNotContact               = $this->createMock(DoNotContact::class);
        $this->doNotContactSubscriber     = new DoNotContactSubscriber($this->doNotContact);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            [
                DoNotContactAddEvent::ADD_DONOT_CONTACT       => ['addDncForLead', 0],
                DoNotContactRemoveEvent::REMOVE_DONOT_CONTACT => ['removeDncForLead', 0],
            ],
            $this->doNotContactSubscriber->getSubscribedEvents()
        );
    }

    public function testAddDncForLeadForNewContacts()
    {
        $lead              = new Lead();
        $doNotContactEvent = new DoNotContactAddEvent($lead, 'email');

        $this->doNotContact->expects($this->once())->method('createDncRecord');
        $this->doNotContact->expects($this->never())->method('addDncForContact');

        $this->doNotContactSubscriber->addDncForLead($doNotContactEvent);
    }

    public function testAddDncForLeadForExistedContacts()
    {
        $lead = new Lead();
        $lead->setId(1);
        $doNotContactEvent = new DoNotContactAddEvent($lead, 'email');

        $this->doNotContact->expects($this->never())->method('createDncRecord');
        $this->doNotContact->expects($this->once())->method('addDncForContact');

        $this->doNotContactSubscriber->addDncForLead($doNotContactEvent);
    }
}
