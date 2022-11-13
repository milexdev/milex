<?php

namespace Milex\LeadBundle\Form\Type;

use Milex\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Milex\CoreBundle\Form\EventListener\FormExitSubscriber;
use Milex\CoreBundle\Form\Type\FormButtonsType;
use Milex\CoreBundle\Helper\DateTimeHelper;
use Milex\LeadBundle\Entity\LeadNote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteType extends AbstractType
{
    /**
     * @var DateTimeHelper
     */
    private $dateHelper;

    public function __construct()
    {
        $this->dateHelper = new DateTimeHelper();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['text' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('lead.note', $options));

        $builder->add(
            'text',
            TextareaType::class,
            [
                'label'      => 'milex.lead.note.form.text',
                'label_attr' => ['class' => 'control-label sr-only'],
                'attr'       => ['class' => 'mousetrap form-control editor', 'rows' => 10, 'autofocus' => 'autofocus'],
            ]
        );

        $builder->add(
            'type',
            ChoiceType::class,
            [
                'label'             => 'milex.lead.note.form.type',
                'choices'           => [
                    'milex.lead.note.type.general' => 'general',
                    'milex.lead.note.type.email'   => 'email',
                    'milex.lead.note.type.call'    => 'call',
                    'milex.lead.note.type.meeting' => 'meeting',
                ],
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $dt   = $options['data']->getDatetime();
        $data = (null == $dt) ? $this->dateHelper->getDateTime() : $dt;

        $builder->add(
            'dateTime',
            DateTimeType::class,
            [
                'label'      => 'milex.core.date.added',
                'label_attr' => ['class' => 'control-label'],
                'widget'     => 'single_text',
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'datetime',
                    'preaddon'    => 'fa fa-calendar',
                ],
                'format' => 'yyyy-MM-dd HH:mm',
                'data'   => $data,
            ]
        );

        $builder->add('buttons', FormButtonsType::class, [
            'apply_text' => false,
            'save_text'  => 'milex.core.form.save',
        ]);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LeadNote::class,
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'leadnote';
    }
}
