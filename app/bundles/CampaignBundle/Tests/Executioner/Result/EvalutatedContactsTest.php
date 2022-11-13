<?php

namespace Milex\CampaignBundle\Tests\Executioner\Result;

use Milex\CampaignBundle\Executioner\Result\EvaluatedContacts;
use Milex\LeadBundle\Entity\Lead;

class EvalutatedContactsTest extends \PHPUnit\Framework\TestCase
{
    public function testPassFail()
    {
        $evaluatedContacts = new EvaluatedContacts();
        $passLead          = new Lead();
        $evaluatedContacts->pass($passLead);

        $failedLead = new Lead();
        $evaluatedContacts->fail($failedLead);

        $passed = $evaluatedContacts->getPassed();
        $failed = $evaluatedContacts->getFailed();

        $this->assertCount(1, $passed);
        $this->assertCount(1, $failed);

        $this->assertTrue($passLead === $passed->first());
        $this->assertTrue($failedLead === $failed->first());
    }
}
