<?php

namespace Milex\PluginBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class IntegrationConfigType.
 */
class IntegrationConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null != $options['integration']) {
            $options['integration']->appendToForm($builder, $options['data'], 'integration');
        }

        if (!empty($options['campaigns'])) {
            $builder->add(
                'campaigns',
                ChoiceType::class,
                [
                    'choices' => array_flip($options['campaigns']),
                    'attr'    => [
                        'class' => 'form-control', 'onchange' => 'Milex.getIntegrationCampaignStatus(this);', ],
                    'label'             => 'milex.plugin.integration.campaigns',
                    'placeholder'       => 'milex.plugin.config.campaign.member.chooseone',
                    'required'          => false,
                    ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['integration']);
        $resolver->setDefaults([
            'label'     => false,
            'campaigns' => [],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'integration_config';
    }
}
