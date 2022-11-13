<?php

namespace Milex\LeadBundle\Form\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class FieldAliasKeyword extends Constraint
{
    public $message = 'milex.lead.field.keyword.invalid';

    public function validatedBy()
    {
        return FieldAliasKeywordValidator::class;
    }
}
