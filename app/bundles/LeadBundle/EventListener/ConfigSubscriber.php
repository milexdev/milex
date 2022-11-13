<?php

namespace Milex\LeadBundle\EventListener;

use Milex\ConfigBundle\ConfigEvents;
use Milex\ConfigBundle\Event\ConfigBuilderEvent;
use Milex\LeadBundle\Form\Type\ConfigCompanyType;
use Milex\LeadBundle\Form\Type\ConfigType;
use Milex\LeadBundle\Form\Type\SegmentConfigType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ConfigEvents::CONFIG_ON_GENERATE => [
                ['onConfigGenerate', 0],
                ['onConfigCompanyGenerate', 0],
            ],
        ];
    }

    public function onConfigGenerate(ConfigBuilderEvent $event)
    {
        $leadParameters = $event->getParametersFromConfig('MilexLeadBundle');
        unset($leadParameters['company_unique_identifiers_operator']);
        $event->addForm([
            'bundle'     => 'LeadBundle',
            'formAlias'  => 'leadconfig',
            'formType'   => ConfigType::class,
            'formTheme'  => 'MilexLeadBundle:FormTheme\Config',
            'parameters' => $leadParameters,
        ]);

        $segmentParameters = $event->getParametersFromConfig('MilexLeadBundle');
        unset($segmentParameters['contact_unique_identifiers_operator'], $segmentParameters['contact_columns'], $segmentParameters['background_import_if_more_rows_than']);
        $event->addForm([
            'bundle'     => 'LeadBundle',
            'formAlias'  => 'segment_config',
            'formType'   => SegmentConfigType::class,
            'formTheme'  => 'MilexLeadBundle:FormTheme\Config',
            'parameters' => $segmentParameters,
        ]);
    }

    public function onConfigCompanyGenerate(ConfigBuilderEvent $event)
    {
        $parameters = $event->getParametersFromConfig('MilexLeadBundle');
        $event->addForm([
            'bundle'     => 'LeadBundle',
            'formAlias'  => 'companyconfig',
            'formType'   => ConfigCompanyType::class,
            'formTheme'  => 'MilexLeadBundle:FormTheme\Config',
            'parameters' => [
                'company_unique_identifiers_operator' => $parameters['company_unique_identifiers_operator'],
            ],
        ]);
    }
}
