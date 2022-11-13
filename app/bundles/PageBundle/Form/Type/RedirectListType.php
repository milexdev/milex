<?php

namespace Milex\PageBundle\Form\Type;

use Milex\CoreBundle\Helper\CoreParametersHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RedirectListType.
 */
class RedirectListType extends AbstractType
{
    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    public function __construct(CoreParametersHelper $coreParametersHelper)
    {
        $this->coreParametersHelper = $coreParametersHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = $this->coreParametersHelper->get('redirect_list_types');
        $choices = (null === $choices) ? [] : array_flip($choices);

        $resolver->setDefaults([
            'choices'     => $choices,
            'expanded'    => false,
            'multiple'    => false,
            'label'       => 'milex.page.form.redirecttype',
            'label_attr'  => ['class' => 'control-label'],
            'placeholder' => false,
            'required'    => false,
            'attr'        => [
                'class' => 'form-control',
            ],
            'feature'           => 'all',
            ]);

        $resolver->setDefined(['feature']);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'redirect_list';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
