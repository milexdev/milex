<?php

namespace Milex\LeadBundle\Tests\Validator\Constraints;

use Milex\LeadBundle\Form\Validator\Constraints\EmailAddress;
use Milex\LeadBundle\Form\Validator\Constraints\EmailAddressValidator;

class EmailAddressTest extends \PHPUnit\Framework\TestCase
{
    public function testValidateBy(): void
    {
        $constraint = new EmailAddress();
        $this->assertEquals(EmailAddressValidator::class, $constraint->validatedBy());
    }
}
