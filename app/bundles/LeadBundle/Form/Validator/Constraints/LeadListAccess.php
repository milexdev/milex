<?php

namespace Milex\LeadBundle\Form\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class LeadListAccess extends Constraint
{
    public $message = 'milex.lead.lists.failed';

    public function validatedBy()
    {
        return 'leadlist_access';
    }
}
