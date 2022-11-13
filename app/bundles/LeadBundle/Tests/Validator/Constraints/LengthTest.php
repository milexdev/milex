<?php

namespace Milex\LeadBundle\Tests\Validator\Constraints;

use Milex\LeadBundle\Validator\Constraints\Length;
use Milex\LeadBundle\Validator\Constraints\LengthValidator;

class LengthTest extends \PHPUnit\Framework\TestCase
{
    public function testValidateBy()
    {
        $constraint = new Length(['min' => 3]);
        $this->assertEquals(LengthValidator::class, $constraint->validatedBy());
    }
}
