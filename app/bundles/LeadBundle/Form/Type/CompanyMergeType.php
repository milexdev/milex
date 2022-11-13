<?php

namespace Milex\LeadBundle\Form\Type;

use Milex\CoreBundle\Form\Type\FormButtonsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CompanyMergeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'company_to_merge',
            CompanyListType::class,
            [
                'multiple'    => false,
                'label'       => 'milex.company.to.merge.into',
                'required'    => true,
                'modal_route' => false,
                'main_entity' => $options['main_entity'],
                'constraints' => [
                    new NotBlank(
                        ['message' => 'milex.company.choosecompany.notblank']
                    ),
                ],
            ]
        );
        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'apply_text' => false,
                'save_text'  => 'milex.lead.merge',
                'save_icon'  => 'fa fa-building',
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(
            ['main_entity']
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'company_merge';
    }
}
