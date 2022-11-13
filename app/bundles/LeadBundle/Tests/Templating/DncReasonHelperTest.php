<?php

namespace Milex\LeadBundle\Tests\Templating;

use Milex\LeadBundle\Entity\DoNotContact;
use Milex\LeadBundle\Exception\UnknownDncReasonException;
use Milex\LeadBundle\Templating\Helper\DncReasonHelper;
use Symfony\Component\Translation\TranslatorInterface;

class DncReasonHelperTest extends \PHPUnit\Framework\TestCase
{
    private $reasonTo = [
        DoNotContact::IS_CONTACTABLE => 'milex.lead.event.donotcontact_contactable',
        DoNotContact::UNSUBSCRIBED   => 'milex.lead.event.donotcontact_unsubscribed',
        DoNotContact::BOUNCED        => 'milex.lead.event.donotcontact_bounced',
        DoNotContact::MANUAL         => 'milex.lead.event.donotcontact_manual',
    ];

    private $translations = [
        'milex.lead.event.donotcontact_contactable'  => 'a',
        'milex.lead.event.donotcontact_unsubscribed' => 'b',
        'milex.lead.event.donotcontact_bounced'      => 'c',
        'milex.lead.event.donotcontact_manual'       => 'd',
    ];

    public function testToText()
    {
        foreach ($this->reasonTo as $reasonId => $translationKey) {
            $translationResult = $this->translations[$translationKey];

            $translator = $this->createMock(TranslatorInterface::class);
            $translator->expects($this->once())
                ->method('trans')
                ->with($translationKey)
                ->willReturn($translationResult);

            $dncReasonHelper = new DncReasonHelper($translator);

            $this->assertSame($translationResult, $dncReasonHelper->toText($reasonId));
        }

        $translator      = $this->createMock(TranslatorInterface::class);
        $dncReasonHelper = new DncReasonHelper($translator);
        $this->expectException(UnknownDncReasonException::class);
        $dncReasonHelper->toText(999);
    }

    public function testGetName()
    {
        $translator      = $this->createMock(TranslatorInterface::class);
        $dncReasonHelper = new DncReasonHelper($translator);
        $this->assertSame('lead_dnc_reason', $dncReasonHelper->getName());
    }
}
