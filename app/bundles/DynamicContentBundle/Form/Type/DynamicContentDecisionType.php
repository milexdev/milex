<?php

namespace Milex\DynamicContentBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class DynamicContentDecisionType.
 */
class DynamicContentDecisionType extends DynamicContentSendType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'dwc_slot_name',
            TextType::class,
            [
                'label'      => 'milex.dynamicContent.send.slot_name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'milex.dynamicContent.send.slot_name.tooltip',
                ],
                'required'    => true,
                'constraints' => [
                    new NotBlank(['message' => 'milex.core.value.required']),
                ],
            ]
        );

        parent::buildForm($builder, $options);

        $builder->add(
            'dynamicContent',
            DynamicContentListType::class,
            [
                'label'      => 'milex.dynamicContent.send.selectDynamicContents.default',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'tooltip'  => 'milex.dynamicContent.choose.dynamicContents',
                    'onchange' => 'Milex.disabledDynamicContentAction()',
                ],
                'where'       => 'e.isCampaignBased = 1', // do not show dwc with filters
                'multiple'    => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(['message' => 'milex.core.value.required']),
                ],
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'dwcdecision_list';
    }
}
