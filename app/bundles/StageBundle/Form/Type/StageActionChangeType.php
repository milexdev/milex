<?php

namespace Milex\StageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class EmailSendType.
 */
class StageActionChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('stage', StageActionListType::class, [
            'label'      => 'milex.stage.selectstage',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'   => 'form-control',
                'tooltip' => 'milex.stage.choose.stage_descr',
            ],
            'multiple'    => false,
            'required'    => true,
            'constraints' => [
                new NotBlank(
                    ['message' => 'milex.core.value.required']
                ),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['update_select']);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'stageaction_change';
    }
}
