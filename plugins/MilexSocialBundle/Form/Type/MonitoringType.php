<?php

namespace MilexPlugin\MilexSocialBundle\Form\Type;

use Milex\CategoryBundle\Form\Type\CategoryListType;
use Milex\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Milex\CoreBundle\Form\Type\FormButtonsType;
use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Milex\LeadBundle\Form\Type\LeadListType;
use MilexPlugin\MilexSocialBundle\Model\MonitoringModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonitoringType extends AbstractType
{
    /** @var MonitoringModel */
    private $monitoringModel;

    public function __construct(MonitoringModel $monitoringModel)
    {
        $this->monitoringModel = $monitoringModel;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));

        $builder->add('title', TextType::class, [
            'label'      => 'milex.core.name',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control'],
        ]);

        $builder->add('description', TextareaType::class, [
            'label'      => 'milex.core.description',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control editor'],
            'required'   => false,
        ]);

        $builder->add('isPublished', YesNoButtonGroupType::class);

        $builder->add('publishUp', DateTimeType::class, [
            'widget'     => 'single_text',
            'label'      => 'milex.core.form.publishup',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'       => 'form-control',
                'data-toggle' => 'datetime',
            ],
            'format'   => 'yyyy-MM-dd HH:mm',
            'required' => false,
        ]);

        $builder->add('publishDown', DateTimeType::class, [
            'widget'     => 'single_text',
            'label'      => 'milex.core.form.publishdown',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'       => 'form-control',
                'data-toggle' => 'datetime',
            ],
            'format'   => 'yyyy-MM-dd HH:mm',
            'required' => false,
        ]);

        $builder->add('networkType', ChoiceType::class, [
            'label'      => 'milex.social.monitoring.type.list',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'    => 'form-control',
                'onchange' => 'Milex.getNetworkFormAction(this)',
            ],
            'choices'           => array_flip((array) $options['networkTypes']), // passed from the controller
            'placeholder'       => 'milex.core.form.chooseone',
        ]);

        // if we have a network type value add in the form
        if (!empty($options['networkType']) && array_key_exists($options['networkType'], $options['networkTypes'])) {
            // get the values from the entity function
            $properties = $options['data']->getProperties();

            $formType = $this->monitoringModel->getFormByType($options['networkType']);

            $builder->add('properties', $formType,
                [
                    'label' => false,
                    'data'  => $properties,
                ]
            );
        }

        $builder->add(
            'lists',
            LeadListType::class,
            [
                'label'      => 'milex.lead.lead.events.addtolists',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'multiple' => true,
                'expanded' => false,
            ]
        );

        //add category
        $builder->add('category', CategoryListType::class, [
            'bundle' => 'plugin:milexSocial',
        ]);

        $builder->add('buttons', FormButtonsType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'data_class' => 'MilexPlugin\MilexSocialBundle\Entity\Monitoring',
            ]);

        // allow network types to be sent through - list
        $resolver->setRequired(['networkTypes']);

        // allow the specific network type - single
        $resolver->setDefined(['networkType']);
    }

    public function getBlockPrefix()
    {
        return 'monitoring';
    }
}
