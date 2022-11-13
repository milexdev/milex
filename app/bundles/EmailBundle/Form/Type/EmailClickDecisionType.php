<?php

namespace Milex\EmailBundle\Form\Type;

use Milex\CoreBundle\Form\Type\SortableListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EmailClickDecisionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'urls',
            SortableListType::class,
            [
                'label'           => 'milex.email.click.urls.contains',
                'option_required' => false,
                'with_labels'     => false,
                'required'        => false,
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'email_click_decision';
    }
}
