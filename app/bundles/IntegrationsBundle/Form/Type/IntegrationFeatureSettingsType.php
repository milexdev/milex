<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Form\Type;

use Milex\IntegrationsBundle\Exception\IntegrationNotFoundException;
use Milex\IntegrationsBundle\Integration\Interfaces\ConfigFormFeatureSettingsInterface;
use Milex\IntegrationsBundle\Integration\Interfaces\ConfigFormSyncInterface;
use Milex\IntegrationsBundle\Integration\Interfaces\IntegrationInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntegrationFeatureSettingsType extends AbstractType
{
    /**
     * @throws IntegrationNotFoundException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $integrationObject = $options['integrationObject'];
        if (!$integrationObject instanceof IntegrationInterface) {
            throw new IntegrationNotFoundException("{$options['integrationObject']} is not recognized");
        }

        if ($integrationObject instanceof ConfigFormFeatureSettingsInterface) {
            $builder->add(
                'integration',
                $integrationObject->getFeatureSettingsConfigFormName(),
                [
                    'label' => false,
                ]
            );
        }

        if ($integrationObject instanceof ConfigFormSyncInterface) {
            $builder->add(
                'sync',
                IntegrationSyncSettingsType::class,
                [
                    'label'             => false,
                    'integrationObject' => $integrationObject,
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'integrationObject',
            ]
        );
    }
}
