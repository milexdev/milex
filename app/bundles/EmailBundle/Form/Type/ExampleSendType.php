<?php

namespace Milex\EmailBundle\Form\Type;

use Milex\CoreBundle\Form\Type\FormButtonsType;
use Milex\CoreBundle\Form\Type\SortableListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;

class ExampleSendType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'emails',
            SortableListType::class,
            [
                'entry_type'       => EmailType::class,
                'label'            => 'milex.email.example_recipients',
                'add_value_button' => 'milex.email.add_recipient',
                'option_notblank'  => false,
            ]
        );

        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'apply_text' => false,
                'save_text'  => 'milex.email.send',
                'save_icon'  => 'fa fa-send',
            ]
        );
    }
}
