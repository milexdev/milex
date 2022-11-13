<?php

namespace MilexPlugin\MilexCrmBundle\Integration;

use Milex\CoreBundle\Helper\InputHelper;
use Milex\LeadBundle\Entity\Company;
use Milex\LeadBundle\Entity\DoNotContact;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Helper\IdentifyCompanyHelper;
use Milex\PluginBundle\Entity\IntegrationEntity;
use Milex\PluginBundle\Entity\IntegrationEntityRepository;
use Milex\PluginBundle\Exception\ApiErrorException;
use MilexPlugin\MilexCrmBundle\Api\SalesforceApi;
use MilexPlugin\MilexCrmBundle\Integration\Salesforce\CampaignMember\Fetcher;
use MilexPlugin\MilexCrmBundle\Integration\Salesforce\CampaignMember\Organizer;
use MilexPlugin\MilexCrmBundle\Integration\Salesforce\Exception\NoObjectsToFetchException;
use MilexPlugin\MilexCrmBundle\Integration\Salesforce\Helper\StateValidationHelper;
use MilexPlugin\MilexCrmBundle\Integration\Salesforce\Object\CampaignMember;
use MilexPlugin\MilexCrmBundle\Integration\Salesforce\ResultsPaginator;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class SalesforceIntegration.
 *
 * @method SalesforceApi getApiHelper
 */
class SalesforceIntegration extends CrmAbstractIntegration
{
    private $objects = [
        'Lead',
        'Contact',
        'Account',
    ];

    /**
     * @var bool
     */
    private $failureFetchingLeads = false;

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'Salesforce';
    }

    /**
     * Get the array key for clientId.
     *
     * @return string
     */
    public function getClientIdKey()
    {
        return 'client_id';
    }

    /**
     * Get the array key for client secret.
     *
     * @return string
     */
    public function getClientSecretKey()
    {
        return 'client_secret';
    }

    /**
     * Get the array key for the auth token.
     *
     * @return string
     */
    public function getAuthTokenKey()
    {
        return 'access_token';
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getRequiredKeyFields()
    {
        return [
            'client_id'     => 'milex.integration.keyfield.consumerid',
            'client_secret' => 'milex.integration.keyfield.consumersecret',
        ];
    }

    /**
     * Get the keys for the refresh token and expiry.
     *
     * @return array
     */
    public function getRefreshTokenKeys()
    {
        return ['refresh_token', ''];
    }

    /**
     * @return array
     */
    public function getSupportedFeatures()
    {
        return ['push_lead', 'get_leads', 'push_leads'];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAccessTokenUrl()
    {
        $config = $this->mergeConfigToFeatureSettings([]);

        if (isset($config['sandbox'][0]) and 'sandbox' === $config['sandbox'][0]) {
            return 'https://test.salesforce.com/services/oauth2/token';
        }

        return 'https://login.salesforce.com/services/oauth2/token';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthenticationUrl()
    {
        $config = $this->mergeConfigToFeatureSettings([]);

        if (isset($config['sandbox'][0]) and 'sandbox' === $config['sandbox'][0]) {
            return 'https://test.salesforce.com/services/oauth2/authorize';
        }

        return 'https://login.salesforce.com/services/oauth2/authorize';
    }

    /**
     * @return string
     */
    public function getAuthScope()
    {
        return 'api refresh_token';
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return sprintf('%s/services/data/v34.0/sobjects', $this->keys['instance_url']);
    }

    /**
     * @return string
     */
    public function getQueryUrl()
    {
        return sprintf('%s/services/data/v34.0', $this->keys['instance_url']);
    }

    /**
     * @return string
     */
    public function getCompositeUrl()
    {
        return sprintf('%s/services/data/v38.0', $this->keys['instance_url']);
    }

    /**
     * {@inheritdoc}
     *
     * @param bool $inAuthorization
     */
    public function getBearerToken($inAuthorization = false)
    {
        if (!$inAuthorization && isset($this->keys[$this->getAuthTokenKey()])) {
            return $this->keys[$this->getAuthTokenKey()];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        return 'oauth2';
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function getDataPriority()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function updateDncByDate()
    {
        $featureSettings = $this->settings->getFeatureSettings();
        if (isset($featureSettings['updateDncByDate'][0]) && 'updateDncByDate' === $featureSettings['updateDncByDate'][0]) {
            return true;
        }

        return false;
    }

    /**
     * Get available company fields for choices in the config UI.
     *
     * @param array $settings
     *
     * @return array
     */
    public function getFormCompanyFields($settings = [])
    {
        return $this->getFormFieldsByObject('company', $settings);
    }

    /**
     * @param array $settings
     *
     * @return array|mixed
     *
     * @throws \Exception
     */
    public function getFormLeadFields($settings = [])
    {
        $leadFields    = $this->getFormFieldsByObject('Lead', $settings);
        $contactFields = $this->getFormFieldsByObject('Contact', $settings);

        return array_merge($leadFields, $contactFields);
    }

    /**
     * @param array $settings
     *
     * @return array|mixed
     *
     * @throws \Exception
     */
    public function getAvailableLeadFields($settings = [])
    {
        $silenceExceptions = (isset($settings['silence_exceptions'])) ? $settings['silence_exceptions'] : true;
        $salesForceObjects = [];

        if (isset($settings['feature_settings']['objects'])) {
            $salesForceObjects = $settings['feature_settings']['objects'];
        } else {
            $salesForceObjects[] = 'Lead';
        }

        $isRequired = function (array $field, $object) {
            return
                ('boolean' !== $field['type'] && empty($field['nillable']) && !in_array($field['name'], ['Status', 'Id', 'CreatedDate'])) ||
                ('Lead' == $object && in_array($field['name'], ['Company'])) ||
                (in_array($object, ['Lead', 'Contact']) && 'Email' === $field['name']);
        };

        $salesFields = [];
        try {
            if (!empty($salesForceObjects) and is_array($salesForceObjects)) {
                foreach ($salesForceObjects as $sfObject) {
                    if ('Account' === $sfObject) {
                        // Match SF object to Milex's
                        $sfObject = 'company';
                    }

                    if (isset($sfObject) and 'Activity' == $sfObject) {
                        continue;
                    }

                    $sfObject = trim($sfObject);
                    // Check the cache first
                    $settings['cache_suffix'] = $cacheSuffix = '.'.$sfObject;
                    if ($fields = parent::getAvailableLeadFields($settings)) {
                        if (('company' === $sfObject && isset($fields['Id'])) || isset($fields['Id__'.$sfObject])) {
                            $salesFields[$sfObject] = $fields;

                            continue;
                        }
                    }

                    if ($this->isAuthorized()) {
                        if (!isset($salesFields[$sfObject])) {
                            $fields = $this->getApiHelper()->getLeadFields($sfObject);
                            if (!empty($fields['fields'])) {
                                foreach ($fields['fields'] as $fieldInfo) {
                                    if ((!$fieldInfo['updateable'] && (!$fieldInfo['calculated'] && !in_array($fieldInfo['name'], ['Id', 'IsDeleted', 'CreatedDate'])))
                                        || !isset($fieldInfo['name'])
                                        || (in_array(
                                                $fieldInfo['type'],
                                                ['reference']
                                            ) && 'AccountId' != $fieldInfo['name'])
                                    ) {
                                        continue;
                                    }
                                    switch ($fieldInfo['type']) {
                                        case 'boolean': $type = 'boolean';
                                            break;
                                        case 'datetime': $type = 'datetime';
                                            break;
                                        case 'date': $type = 'date';
                                            break;
                                        default: $type = 'string';
                                    }
                                    if ('company' !== $sfObject) {
                                        if ('AccountId' == $fieldInfo['name']) {
                                            $fieldInfo['label'] = 'Company';
                                        }
                                        $salesFields[$sfObject][$fieldInfo['name'].'__'.$sfObject] = [
                                            'type'        => $type,
                                            'label'       => $sfObject.'-'.$fieldInfo['label'],
                                            'required'    => $isRequired($fieldInfo, $sfObject),
                                            'group'       => $sfObject,
                                            'optionLabel' => $fieldInfo['label'],
                                        ];

                                        // CreateDate can be updatable just in Milex
                                        if (in_array($fieldInfo['name'], ['CreatedDate'])) {
                                            $salesFields[$sfObject][$fieldInfo['name'].'__'.$sfObject]['update_milex'] = 1;
                                        }
                                    } else {
                                        $salesFields[$sfObject][$fieldInfo['name']] = [
                                            'type'     => $type,
                                            'label'    => $fieldInfo['label'],
                                            'required' => $isRequired($fieldInfo, $sfObject),
                                        ];
                                    }
                                }

                                $this->cache->set('leadFields'.$cacheSuffix, $salesFields[$sfObject]);
                            }
                        }

                        asort($salesFields[$sfObject]);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logIntegrationError($e);

            if (!$silenceExceptions) {
                throw $e;
            }
        }

        return $salesFields;
    }

    /**
     * {@inheritdoc}
     *
     * @param $section
     *
     * @return array
     */
    public function getFormNotes($section)
    {
        if ('authorization' == $section) {
            return ['milex.salesforce.form.oauth_requirements', 'warning'];
        }

        return parent::getFormNotes($section);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    public function getFetchQuery($params)
    {
        return $params;
    }

    /**
     * @param       $data
     * @param       $object
     * @param array $params
     *
     * @return array
     */
    public function amendLeadDataBeforeMilexPopulate($data, $object, $params = [])
    {
        $updated               = 0;
        $created               = 0;
        $counter               = 0;
        $entity                = null;
        $detachClass           = null;
        $milexObjectReference = null;
        $integrationMapping    = [];

        if (isset($data['records']) and 'Activity' !== $object) {
            foreach ($data['records'] as $record) {
                $this->logger->debug('SALESFORCE: amendLeadDataBeforeMilexPopulate record '.var_export($record, true));
                if (isset($params['progress'])) {
                    $params['progress']->advance();
                }

                $dataObject = [];
                if (isset($record['attributes']['type']) && 'Account' == $record['attributes']['type']) {
                    $newName = '';
                } else {
                    $newName = '__'.$object;
                }

                foreach ($record as $key => $item) {
                    if (is_bool($item)) {
                        $dataObject[$key.$newName] = (int) $item;
                    } else {
                        $dataObject[$key.$newName] = $item;
                    }
                }

                if ($dataObject) {
                    $entity = false;
                    switch ($object) {
                        case 'Contact':
                            if (isset($dataObject['Email__Contact'])) {
                                // Sanitize email to make sure we match it
                                // correctly against milex emails
                                $dataObject['Email__Contact'] = InputHelper::email($dataObject['Email__Contact']);
                            }

                            //get company from account id and assign company name
                            if (isset($dataObject['AccountId__'.$object])) {
                                $companyName = $this->getCompanyName($dataObject['AccountId__'.$object], 'Name');

                                if ($companyName) {
                                    $dataObject['AccountId__'.$object] = $companyName;
                                } else {
                                    unset($dataObject['AccountId__'.$object]); //no company was found in Salesforce
                                }
                            }
                            // no break
                        case 'Lead':
                            // Set owner so that it maps if configured to do so
                            if (!empty($dataObject['Owner__Lead']['Email'])) {
                                $dataObject['owner_email'] = $dataObject['Owner__Lead']['Email'];
                            } elseif (!empty($dataObject['Owner__Contact']['Email'])) {
                                $dataObject['owner_email'] = $dataObject['Owner__Contact']['Email'];
                            }

                            if (isset($dataObject['Email__Lead'])) {
                                // Sanitize email to make sure we match it
                                // correctly against milex_leads emails
                                $dataObject['Email__Lead'] = InputHelper::email($dataObject['Email__Lead']);
                            }

                            // normalize multiselect field
                            foreach ($dataObject as &$dataO) {
                                if (is_string($dataO)) {
                                    $dataO = str_replace(';', '|', $dataO);
                                }
                            }
                            $entity                = $this->getMilexLead($dataObject, true, null, null, $object);
                            $milexObjectReference = 'lead';
                            $detachClass           = Lead::class;

                            break;
                        case 'Account':
                            $entity                = $this->getMilexCompany($dataObject, 'Account');
                            $milexObjectReference = 'company';
                            $detachClass           = Company::class;

                            break;
                        default:
                            $this->logIntegrationError(
                                new \Exception(
                                    sprintf('Received an unexpected object without an internalObjectReference "%s"', $object)
                                )
                            );
                            break;
                    }

                    if (!$entity) {
                        continue;
                    }

                    $integrationMapping[$entity->getId()] = [
                        'entity'                => $entity,
                        'integration_entity_id' => $record['Id'],
                    ];

                    if (method_exists($entity, 'isNewlyCreated') && $entity->isNewlyCreated()) {
                        ++$created;
                    } else {
                        ++$updated;
                    }

                    ++$counter;

                    if ($counter >= 100) {
                        // Persist integration entities
                        $this->buildIntegrationEntities($integrationMapping, $object, $milexObjectReference, $params);
                        $counter = 0;
                        $this->em->clear($detachClass);
                        $integrationMapping = [];
                    }
                }
            }

            if (count($integrationMapping)) {
                // Persist integration entities
                $this->buildIntegrationEntities($integrationMapping, $object, $milexObjectReference, $params);
                $this->em->clear($detachClass);
            }

            unset($data['records']);
            $this->logger->debug('SALESFORCE: amendLeadDataBeforeMilexPopulate response '.var_export($data, true));

            unset($data);
            $this->persistIntegrationEntities = [];
            unset($dataObject);
        }

        return [$updated, $created];
    }

    /**
     * @param FormBuilder $builder
     * @param array       $data
     * @param string      $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ('features' == $formArea) {
            $builder->add(
                'sandbox',
                ChoiceType::class,
                [
                    'choices' => [
                        'milex.salesforce.sandbox' => 'sandbox',
                    ],
                    'expanded'          => true,
                    'multiple'          => true,
                    'label'             => 'milex.salesforce.form.sandbox',
                    'label_attr'        => ['class' => 'control-label'],
                    'placeholder'       => false,
                    'required'          => false,
                    'attr'              => [
                        'onclick' => 'Milex.postForm(mQuery(\'form[name="integration_details"]\'),\'\');',
                    ],
                ]
            );

            $builder->add(
                'updateOwner',
                ChoiceType::class,
                [
                    'choices' => [
                        'milex.salesforce.updateOwner' => 'updateOwner',
                    ],
                    'expanded'          => true,
                    'multiple'          => true,
                    'label'             => 'milex.salesforce.form.updateOwner',
                    'label_attr'        => ['class' => 'control-label'],
                    'placeholder'       => false,
                    'required'          => false,
                    'attr'              => [
                        'onclick' => 'Milex.postForm(mQuery(\'form[name="integration_details"]\'),\'\');',
                    ],
                ]
            );
            $builder->add(
                'updateBlanks',
                ChoiceType::class,
                [
                    'choices' => [
                        'milex.integrations.blanks' => 'updateBlanks',
                    ],
                    'expanded'          => true,
                    'multiple'          => true,
                    'label'             => 'milex.integrations.form.blanks',
                    'label_attr'        => ['class' => 'control-label'],
                    'placeholder'       => false,
                    'required'          => false,
                ]
            );
            $builder->add(
                'updateDncByDate',
                ChoiceType::class,
                [
                    'choices' => [
                        'milex.integrations.update.dnc.by.date' => 'updateDncByDate',
                    ],
                    'expanded'          => true,
                    'multiple'          => true,
                    'label'             => 'milex.integrations.form.update.dnc.by.date.label',
                    'label_attr'        => ['class' => 'control-label'],
                    'placeholder'       => false,
                    'required'          => false,
                ]
            );

            $builder->add(
                'objects',
                ChoiceType::class,
                [
                    'choices' => [
                        'milex.salesforce.object.lead'     => 'Lead',
                        'milex.salesforce.object.contact'  => 'Contact',
                        'milex.salesforce.object.company'  => 'company',
                        'milex.salesforce.object.activity' => 'Activity',
                    ],
                    'expanded'          => true,
                    'multiple'          => true,
                    'label'             => 'milex.salesforce.form.objects_to_pull_from',
                    'label_attr'        => ['class' => ''],
                    'placeholder'       => false,
                    'required'          => false,
                ]
            );

            $builder->add(
                'activityEvents',
                ChoiceType::class,
                [
                    'choices'           => array_flip($this->leadModel->getEngagementTypes()), // Choice type expects labels as keys
                    'label'             => 'milex.salesforce.form.activity_included_events',
                    'label_attr'        => [
                        'class'       => 'control-label',
                        'data-toggle' => 'tooltip',
                        'title'       => $this->translator->trans('milex.salesforce.form.activity.events.tooltip'),
                    ],
                    'multiple'   => true,
                    'empty_data' => ['point.gained', 'form.submitted', 'email.read'], // BC with pre 2.11.0
                    'required'   => false,
                ]
            );

            $builder->add(
                'namespace',
                TextType::class,
                [
                    'label'      => 'milex.salesforce.form.namespace_prefix',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => ['class' => 'form-control'],
                    'required'   => false,
                ]
            );
        }
    }

    /**
     * @param array $fields
     * @param array $keys
     * @param mixed $object
     *
     * @return array
     */
    public function prepareFieldsForSync($fields, $keys, $object = null)
    {
        $leadFields = [];
        if (null === $object) {
            $object = 'Lead';
        }

        $objects = (!is_array($object)) ? [$object] : $object;
        if (is_string($object) && 'Account' === $object) {
            return isset($fields['companyFields']) ? $fields['companyFields'] : $fields;
        }

        if (isset($fields['leadFields'])) {
            $fields = $fields['leadFields'];
            $keys   = array_keys($fields);
        }

        foreach ($objects as $obj) {
            if (!isset($leadFields[$obj])) {
                $leadFields[$obj] = [];
            }

            foreach ($keys as $key) {
                if (strpos($key, '__'.$obj)) {
                    $newKey = str_replace('__'.$obj, '', $key);
                    if ('Id' === $newKey) {
                        // Don't map Id for push
                        continue;
                    }

                    $leadFields[$obj][$newKey] = $fields[$key];
                }
            }
        }

        return (is_array($object)) ? $leadFields : $leadFields[$object];
    }

    /**
     * @param \Milex\LeadBundle\Entity\Lead $lead
     * @param array                          $config
     *
     * @return array|bool
     */
    public function pushLead($lead, $config = [])
    {
        $config = $this->mergeConfigToFeatureSettings($config);

        if (empty($config['leadFields'])) {
            return [];
        }

        $mappedData = $this->mapContactDataForPush($lead, $config);

        // No fields are mapped so bail
        if (empty($mappedData)) {
            return false;
        }

        try {
            if ($this->isAuthorized()) {
                $existingPersons = $this->getApiHelper()->getPerson(
                    [
                        'Lead'    => isset($mappedData['Lead']['create']) ? $mappedData['Lead']['create'] : null,
                        'Contact' => isset($mappedData['Contact']['create']) ? $mappedData['Contact']['create'] : null,
                    ]
                );

                $personFound = false;
                $people      = [
                    'Contact' => [],
                    'Lead'    => [],
                ];

                foreach (['Contact', 'Lead'] as $object) {
                    if (!empty($existingPersons[$object])) {
                        $fieldsToUpdate = $mappedData[$object]['update'];
                        $fieldsToUpdate = $this->getBlankFieldsToUpdate($fieldsToUpdate, $existingPersons[$object], $mappedData, $config);
                        $personFound    = true;
                        foreach ($existingPersons[$object] as $person) {
                            if (!empty($fieldsToUpdate)) {
                                if (isset($fieldsToUpdate['AccountId'])) {
                                    $accountId = $this->getCompanyName($fieldsToUpdate['AccountId'], 'Id', 'Name');
                                    if (!$accountId) {
                                        //company was not found so create a new company in Salesforce
                                        $company = $lead->getPrimaryCompany();
                                        if (!empty($company)) {
                                            $company   = $this->companyModel->getEntity($company['id']);
                                            $sfCompany = $this->pushCompany($company);
                                            if ($sfCompany) {
                                                $fieldsToUpdate['AccountId'] = key($sfCompany);
                                            }
                                        }
                                    } else {
                                        $fieldsToUpdate['AccountId'] = $accountId;
                                    }
                                }

                                $personData = $this->getApiHelper()->updateObject($fieldsToUpdate, $object, $person['Id']);
                            }

                            $people[$object][$person['Id']] = $person['Id'];
                        }
                    }

                    if ('Lead' === $object && !$personFound && isset($mappedData[$object]['create'])) {
                        $personData                         = $this->getApiHelper()->createLead($mappedData[$object]['create']);
                        $people[$object][$personData['Id']] = $personData['Id'];
                        $personFound                        = true;
                    }

                    if (isset($personData['Id'])) {
                        /** @var IntegrationEntityRepository $integrationEntityRepo */
                        $integrationEntityRepo = $this->em->getRepository('MilexPluginBundle:IntegrationEntity');
                        $integrationId         = $integrationEntityRepo->getIntegrationsEntityId('Salesforce', $object, 'lead', $lead->getId());

                        $integrationEntity = (empty($integrationId))
                            ? $this->createIntegrationEntity($object, $personData['Id'], 'lead', $lead->getId(), [], false)
                            :
                            $this->em->getReference('MilexPluginBundle:IntegrationEntity', $integrationId[0]['id']);

                        $integrationEntity->setLastSyncDate($this->getLastSyncDate());
                        $integrationEntityRepo->saveEntity($integrationEntity);
                    }
                }

                // Return success if any Contact or Lead was updated or created
                return ($personFound) ? $people : false;
            }
        } catch (\Exception $e) {
            if ($e instanceof ApiErrorException) {
                $e->setContact($lead);
            }

            $this->logIntegrationError($e);
        }

        return false;
    }

    /**
     * @param \Milex\LeadBundle\Entity\Company $company
     * @param array                             $config
     *
     * @return array|bool
     */
    public function pushCompany($company, $config = [])
    {
        $config = $this->mergeConfigToFeatureSettings($config);

        if (empty($config['companyFields']) || !$company) {
            return [];
        }
        $object     = 'company';
        $mappedData = $this->mapCompanyDataForPush($company, $config);

        // No fields are mapped so bail
        if (empty($mappedData)) {
            return false;
        }

        try {
            if ($this->isAuthorized()) {
                $existingCompanies = $this->getApiHelper()->getCompany(
                    [
                        $object => $mappedData[$object]['create'],
                    ]
                );
                $companyFound = false;
                $companies    = [];

                if (!empty($existingCompanies[$object])) {
                    $fieldsToUpdate = $mappedData[$object]['update'];

                    $fieldsToUpdate = $this->getBlankFieldsToUpdate($fieldsToUpdate, $existingCompanies[$object], $mappedData, $config);
                    $companyFound   = true;

                    foreach ($existingCompanies[$object] as $sfCompany) {
                        if (!empty($fieldsToUpdate)) {
                            $companyData = $this->getApiHelper()->updateObject($fieldsToUpdate, $object, $sfCompany['Id']);
                        }
                        $companies[$sfCompany['Id']] = $sfCompany['Id'];
                    }
                }

                if (!$companyFound) {
                    $companyData                   = $this->getApiHelper()->createObject($mappedData[$object]['create'], 'Account');
                    $companies[$companyData['Id']] = $companyData['Id'];
                    $companyFound                  = true;
                }

                if (isset($companyData['Id'])) {
                    /** @var IntegrationEntityRepository $integrationEntityRepo */
                    $integrationEntityRepo = $this->em->getRepository('MilexPluginBundle:IntegrationEntity');
                    $integrationId         = $integrationEntityRepo->getIntegrationsEntityId('Salesforce', $object, 'company', $company->getId());

                    $integrationEntity = (empty($integrationId))
                        ? $this->createIntegrationEntity($object, $companyData['Id'], 'lead', $company->getId(), [], false)
                        :
                        $this->em->getReference('MilexPluginBundle:IntegrationEntity', $integrationId[0]['id']);

                    $integrationEntity->setLastSyncDate($this->getLastSyncDate());
                    $integrationEntityRepo->saveEntity($integrationEntity);
                }

                // Return success if any company was updated or created
                return ($companyFound) ? $companies : false;
            }
        } catch (\Exception $e) {
            $this->logIntegrationError($e);
        }

        return false;
    }

    /**
     * @param array  $params
     * @param null   $query
     * @param null   $executed
     * @param array  $result
     * @param string $object
     *
     * @return array|null
     */
    public function getLeads($params = [], $query = null, &$executed = null, $result = [], $object = 'Lead')
    {
        if (!$query) {
            $query = $this->getFetchQuery($params);
        }

        if (!is_array($executed)) {
            $executed = [
                0 => 0,
                1 => 0,
            ];
        }

        try {
            if ($this->isAuthorized()) {
                $progress  = null;
                $paginator = new ResultsPaginator($this->logger, $this->keys['instance_url']);

                while (true) {
                    $result = $this->getApiHelper()->getLeads($query, $object);
                    $paginator->setResults($result);

                    if (isset($params['output']) && !isset($params['progress'])) {
                        $progress = new ProgressBar($params['output'], $paginator->getTotal());
                        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% ('.$object.')');

                        $params['progress'] = $progress;
                    }

                    list($justUpdated, $justCreated) = $this->amendLeadDataBeforeMilexPopulate($result, $object, $params);

                    $executed[0] += $justUpdated;
                    $executed[1] += $justCreated;

                    if (!$nextUrl = $paginator->getNextResultsUrl()) {
                        // No more records to fetch
                        break;
                    }

                    $query['nextUrl']  = $nextUrl;
                }

                if ($progress) {
                    $progress->finish();
                }
            }
        } catch (\Exception $e) {
            $this->logIntegrationError($e);

            $this->failureFetchingLeads = $e->getMessage();
        }

        $this->logger->debug('SALESFORCE: '.$this->getApiHelper()->getRequestCounter().' API requests made for getLeads: '.$object);

        return $executed;
    }

    /**
     * @param array $params
     * @param null  $query
     * @param null  $executed
     *
     * @return array|null
     */
    public function getCompanies($params = [], $query = null, $executed = null)
    {
        return $this->getLeads($params, $query, $executed, [], 'Account');
    }

    /**
     * @param array $params
     *
     * @return int|null
     *
     * @throws \Exception
     */
    public function pushLeadActivity($params = [])
    {
        $executed = null;

        $query  = $this->getFetchQuery($params);
        $config = $this->mergeConfigToFeatureSettings([]);

        /** @var SalesforceApi $apiHelper */
        $apiHelper = $this->getApiHelper();

        $salesForceObjects[] = 'Lead';
        if (isset($config['objects']) && !empty($config['objects'])) {
            $salesForceObjects = $config['objects'];
        }

        // Ensure that Contact is attempted before Lead
        sort($salesForceObjects);

        /** @var IntegrationEntityRepository $integrationEntityRepo */
        $integrationEntityRepo = $this->em->getRepository('MilexPluginBundle:IntegrationEntity');
        $startDate             = new \DateTime($query['start']);
        $endDate               = new \DateTime($query['end']);
        $limit                 = 100;

        foreach ($salesForceObjects as $object) {
            if (!in_array($object, ['Contact', 'Lead'])) {
                continue;
            }

            try {
                if ($this->isAuthorized()) {
                    // Get first batch
                    $start         = 0;
                    $salesForceIds = $integrationEntityRepo->getIntegrationsEntityId(
                        'Salesforce',
                        $object,
                        'lead',
                        null,
                        $startDate->format('Y-m-d H:m:s'),
                        $endDate->format('Y-m-d H:m:s'),
                        true,
                        $start,
                        $limit
                    );
                    while (!empty($salesForceIds)) {
                        $executed += count($salesForceIds);

                        // Extract a list of lead Ids
                        $leadIds = [];
                        $sfIds   = [];
                        foreach ($salesForceIds as $ids) {
                            $leadIds[] = $ids['internal_entity_id'];
                            $sfIds[]   = $ids['integration_entity_id'];
                        }

                        // Collect lead activity for this batch
                        $leadActivity = $this->getLeadData(
                            $startDate,
                            $endDate,
                            $leadIds
                        );

                        $this->logger->debug('SALESFORCE: Syncing activity for '.count($leadActivity).' contacts ('.implode(', ', array_keys($leadActivity)).')');
                        $this->logger->debug('SALESFORCE: Syncing activity for '.var_export($sfIds, true));

                        $salesForceLeadData = [];
                        foreach ($salesForceIds as $ids) {
                            $leadId = $ids['internal_entity_id'];
                            if (isset($leadActivity[$leadId])) {
                                $sfId                                 = $ids['integration_entity_id'];
                                $salesForceLeadData[$sfId]            = $leadActivity[$leadId];
                                $salesForceLeadData[$sfId]['id']      = $ids['integration_entity_id'];
                                $salesForceLeadData[$sfId]['leadId']  = $ids['internal_entity_id'];
                                $salesForceLeadData[$sfId]['leadUrl'] = $this->router->generate(
                                    'milex_plugin_timeline_view',
                                    ['integration' => 'Salesforce', 'leadId' => $leadId],
                                    UrlGeneratorInterface::ABSOLUTE_URL
                                );
                            } else {
                                $this->logger->debug('SALESFORCE: No activity found for contact ID '.$leadId);
                            }
                        }

                        if (!empty($salesForceLeadData)) {
                            $apiHelper->createLeadActivity($salesForceLeadData, $object);
                        } else {
                            $this->logger->debug('SALESFORCE: No contact activity to sync');
                        }

                        // Get the next batch
                        $start += $limit;
                        $salesForceIds = $integrationEntityRepo->getIntegrationsEntityId(
                            'Salesforce',
                            $object,
                            'lead',
                            null,
                            $startDate->format('Y-m-d H:m:s'),
                            $endDate->format('Y-m-d H:m:s'),
                            true,
                            $start,
                            $limit
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->logIntegrationError($e);
            }
        }

        return $executed;
    }

    /**
     * Return key recognized by integration.
     *
     * @param $key
     * @param $field
     *
     * @return mixed
     */
    public function convertLeadFieldKey($key, $field)
    {
        $search = [];
        foreach ($this->objects as $object) {
            $search[] = '__'.$object;
        }

        return str_replace($search, '', $key);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function pushLeads($params = [])
    {
        $limit                   = (isset($params['limit'])) ? $params['limit'] : 100;
        list($fromDate, $toDate) = $this->getSyncTimeframeDates($params);
        $config                  = $this->mergeConfigToFeatureSettings($params);
        $integrationEntityRepo   = $this->getIntegrationEntityRepository();

        $totalUpdated = 0;
        $totalCreated = 0;
        $totalErrors  = 0;

        list($fieldMapping, $milexLeadFieldString, $supportedObjects) = $this->prepareFieldsForPush($config);

        if (empty($fieldMapping)) {
            return [0, 0, 0, 0];
        }

        $originalLimit = $limit;
        $progress      = false;

        // Get a total number of contacts to be updated and/or created for the progress counter
        $totalToUpdate = array_sum(
            $integrationEntityRepo->findLeadsToUpdate(
                'Salesforce',
                'lead',
                $milexLeadFieldString,
                false,
                $fromDate,
                $toDate,
                $supportedObjects,
                []
            )
        );
        $totalToCreate = (in_array('Lead', $supportedObjects)) ? $integrationEntityRepo->findLeadsToCreate(
            'Salesforce',
            $milexLeadFieldString,
            false,
            $fromDate,
            $toDate
        ) : 0;
        $totalCount = $totalToProcess = $totalToCreate + $totalToUpdate;

        if (defined('IN_MAUTIC_CONSOLE')) {
            // start with update
            if ($totalToUpdate + $totalToCreate) {
                $output = new ConsoleOutput();
                $output->writeln("About $totalToUpdate to update and about $totalToCreate to create/update");
                $progress = new ProgressBar($output, $totalCount);
            }
        }

        // Start with contacts so we know who is a contact when we go to process converted leads
        if (count($supportedObjects) > 1) {
            $sfObject = 'Contact';
        } else {
            // Only Lead or Contact is enabled so start with which ever that is
            reset($supportedObjects);
            $sfObject = key($supportedObjects);
        }
        $noMoreUpdates   = false;
        $trackedContacts = [
            'Contact' => [],
            'Lead'    => [],
        ];

        // Loop to maximize composite that may include updating contacts, updating leads, and creating leads
        while ($totalCount > 0) {
            $limit           = $originalLimit;
            $milexData      = [];
            $checkEmailsInSF = [];
            $leadsToSync     = [];
            $processedLeads  = [];

            // Process the updates
            if (!$noMoreUpdates) {
                $noMoreUpdates = $this->getMilexContactsToUpdate(
                    $checkEmailsInSF,
                    $milexLeadFieldString,
                    $sfObject,
                    $trackedContacts,
                    $limit,
                    $fromDate,
                    $toDate,
                    $totalCount
                );

                if ($noMoreUpdates && 'Contact' === $sfObject && isset($supportedObjects['Lead'])) {
                    // Try Leads
                    $sfObject      = 'Lead';
                    $noMoreUpdates = $this->getMilexContactsToUpdate(
                        $checkEmailsInSF,
                        $milexLeadFieldString,
                        $sfObject,
                        $trackedContacts,
                        $limit,
                        $fromDate,
                        $toDate,
                        $totalCount
                    );
                }

                if ($limit) {
                    // Mainly done for test mocking purposes
                    $limit = $this->getSalesforceSyncLimit($checkEmailsInSF, $limit);
                }
            }

            // If there is still room - grab Milex leads to create if the Lead object is enabled
            $sfEntityRecords = [];
            if ('Lead' === $sfObject && (null === $limit || $limit > 0) && !empty($milexLeadFieldString)) {
                try {
                    $sfEntityRecords = $this->getMilexContactsToCreate(
                        $checkEmailsInSF,
                        $fieldMapping,
                        $milexLeadFieldString,
                        $limit,
                        $fromDate,
                        $toDate,
                        $totalCount,
                        $progress
                    );
                } catch (ApiErrorException $exception) {
                    $this->cleanupFromSync($leadsToSync, $exception);
                }
            } elseif ($checkEmailsInSF) {
                $sfEntityRecords = $this->getSalesforceObjectsByEmails($sfObject, $checkEmailsInSF, implode(',', array_keys($fieldMapping[$sfObject]['create'])));

                if (!isset($sfEntityRecords['records'])) {
                    // Something is wrong so throw an exception to prevent creating a bunch of new leads
                    $this->cleanupFromSync(
                        $leadsToSync,
                        json_encode($sfEntityRecords)
                    );
                }
            }

            $this->pushLeadDoNotContactByDate('email', $checkEmailsInSF, $sfObject, $params);

            // We're done
            if (!$checkEmailsInSF) {
                break;
            }

            $this->prepareMilexContactsToUpdate(
                $milexData,
                $checkEmailsInSF,
                $processedLeads,
                $trackedContacts,
                $leadsToSync,
                $fieldMapping,
                $milexLeadFieldString,
                $sfEntityRecords,
                $progress
            );

            // Only create left over if Lead object is enabled in integration settings
            if ($checkEmailsInSF && isset($fieldMapping['Lead'])) {
                $this->prepareMilexContactsToCreate(
                    $milexData,
                    $checkEmailsInSF,
                    $processedLeads,
                    $fieldMapping
                );
            }
            // Persist pending changes
            $this->cleanupFromSync($leadsToSync);
            // Make the request
            $this->makeCompositeRequest($milexData, $totalUpdated, $totalCreated, $totalErrors);

            // Stop gap - if 100% let's kill the script
            if ($progress && $progress->getProgressPercent() >= 1) {
                break;
            }
        }

        if ($progress) {
            $progress->finish();
            $output->writeln('');
        }

        $this->logger->debug('SALESFORCE: '.$this->getApiHelper()->getRequestCounter().' API requests made for pushLeads');

        // Assume that those not touched are ignored due to not having matching fields, duplicates, etc
        $totalIgnored = $totalToProcess - ($totalUpdated + $totalCreated + $totalErrors);

        return [$totalUpdated, $totalCreated, $totalErrors, $totalIgnored];
    }

    /**
     * @param $lead
     *
     * @return array
     */
    public function getSalesforceLeadId($lead)
    {
        $config                = $this->mergeConfigToFeatureSettings([]);
        $integrationEntityRepo = $this->getIntegrationEntityRepository();

        if (isset($config['objects'])) {
            //try searching for lead as this has been changed before in updated done to the plugin
            if (false !== array_search('Contact', $config['objects'])) {
                $resultContact = $integrationEntityRepo->getIntegrationsEntityId('Salesforce', 'Contact', 'lead', $lead->getId());

                if ($resultContact) {
                    return $resultContact;
                }
            }
        }

        return $integrationEntityRepo->getIntegrationsEntityId('Salesforce', 'Lead', 'lead', $lead->getId());
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function getCampaigns()
    {
        $campaigns = [];
        try {
            $campaigns = $this->getApiHelper()->getCampaigns();
        } catch (\Exception $e) {
            $this->logIntegrationError($e);
        }

        return $campaigns;
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function getCampaignChoices()
    {
        $choices   = [];
        $campaigns = $this->getCampaigns();

        if (!empty($campaigns['records'])) {
            foreach ($campaigns['records'] as $campaign) {
                $choices[] = [
                    'value' => $campaign['Id'],
                    'label' => $campaign['Name'],
                ];
            }
        }

        return $choices;
    }

    /**
     * @param $campaignId
     *
     * @throws \Exception
     */
    public function getCampaignMembers($campaignId)
    {
        $this->failureFetchingLeads = false;

        /** @var IntegrationEntityRepository $integrationEntityRepo */
        $integrationEntityRepo = $this->em->getRepository('MilexPluginBundle:IntegrationEntity');
        $mixedFields           = $this->getIntegrationSettings()->getFeatureSettings();

        // Get the last time the campaign was synced to prevent resyncing the entire SF campaign
        $cacheKey     = $this->getName().'.CampaignSync.'.$campaignId;
        $lastSyncDate = $this->getCache()->get($cacheKey);
        $syncStarted  = (new \DateTime())->format('c');

        if (false === $lastSyncDate) {
            // Sync all records
            $lastSyncDate = null;
        }

        // Consume in batches
        $paginator      = new ResultsPaginator($this->logger, $this->keys['instance_url']);
        $nextRecordsUrl = null;

        while (true) {
            try {
                $results = $this->getApiHelper()->getCampaignMembers($campaignId, $lastSyncDate, $nextRecordsUrl);
                $paginator->setResults($results);

                $organizer = new Organizer($results['records']);
                $fetcher   = new Fetcher($integrationEntityRepo, $organizer, $campaignId);

                // Create Milex contacts from Campaign Members if they don't already exist
                foreach (['Contact', 'Lead'] as $object) {
                    $fields = $this->getMixedLeadFields($mixedFields, $object);

                    try {
                        $query = $fetcher->getQueryForUnknownObjects($fields, $object);
                        $this->getLeads([], $query, $executed, [], $object);

                        if ($this->failureFetchingLeads) {
                            // Something failed while fetching the leads (i.e API error limit) so we have to fail here to prevent the campaign
                            // from caching the timestamp that will cause contacts to not be pulled/added to the segment
                            throw new ApiErrorException($this->failureFetchingLeads);
                        }
                    } catch (NoObjectsToFetchException $exception) {
                        // No more IDs to fetch so break and continue on
                        continue;
                    }
                }

                // Create integration entities for members we aren't already tracking
                $unknownMembers  = $fetcher->getUnknownCampaignMembers();
                $persistEntities = [];
                $counter         = 0;

                foreach ($unknownMembers as $milexContactId) {
                    $persistEntities[] = $this->createIntegrationEntity(
                        CampaignMember::OBJECT,
                        $campaignId,
                        'lead',
                        $milexContactId,
                        [],
                        false
                    );

                    ++$counter;

                    if (20 === $counter) {
                        // Batch to control RAM use
                        $this->em->getRepository('MilexPluginBundle:IntegrationEntity')->saveEntities($persistEntities);
                        $this->em->clear(IntegrationEntity::class);
                        $persistEntities = [];
                        $counter         = 0;
                    }
                }

                // Catch left overs
                if ($persistEntities) {
                    $this->em->getRepository('MilexPluginBundle:IntegrationEntity')->saveEntities($persistEntities);
                    $this->em->clear(IntegrationEntity::class);
                }

                unset($unknownMembers, $fetcher, $organizer, $persistEntities);

                // Do we continue?
                if (!$nextRecordsUrl = $paginator->getNextResultsUrl()) {
                    // No more results to fetch

                    // Store the latest sync date at the end in case something happens during the actual sync process and it needs to be re-ran
                    $this->cache->set($cacheKey, $syncStarted);

                    break;
                }
            } catch (\Exception $e) {
                $this->logIntegrationError($e);

                break;
            }
        }
    }

    /**
     * @param $fields
     * @param $object
     *
     * @return array
     */
    public function getMixedLeadFields($fields, $object)
    {
        $mixedFields = array_filter($fields['leadFields']);
        $fields      = [];
        foreach ($mixedFields as $sfField => $mField) {
            if (false !== strpos($sfField, '__'.$object)) {
                $fields[] = str_replace('__'.$object, '', $sfField);
            }
            if (false !== strpos($sfField, '-'.$object)) {
                $fields[] = str_replace('-'.$object, '', $sfField);
            }
        }

        return $fields;
    }

    /**
     * @param $campaignId
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getCampaignMemberStatus($campaignId)
    {
        $campaignMemberStatus = [];
        try {
            $campaignMemberStatus = $this->getApiHelper()->getCampaignMemberStatus($campaignId);
        } catch (\Exception $e) {
            $this->logIntegrationError($e);
        }

        return $campaignMemberStatus;
    }

    /**
     * @param $campaignId
     * @param $status
     *
     * @return array
     */
    public function pushLeadToCampaign(Lead $lead, $campaignId, $status = '', $personIds = null)
    {
        if (empty($personIds)) {
            // personIds should have been generated by pushLead()

            return false;
        }

        $milexData = [];
        $objectId   = null;

        /** @var IntegrationEntityRepository $integrationEntityRepo */
        $integrationEntityRepo = $this->em->getRepository('MilexPluginBundle:IntegrationEntity');

        $body = [
            'Status' => $status,
        ];
        $object = 'CampaignMember';
        $url    = '/services/data/v38.0/sobjects/'.$object;

        if (!empty($lead->getEmail())) {
            $pushPeople = [];
            $pushObject = null;
            if (!empty($personIds)) {
                // Give precendence to Contact CampaignMembers
                if (!empty($personIds['Contact'])) {
                    $pushObject      = 'Contact';
                    $campaignMembers = $this->getApiHelper()->checkCampaignMembership($campaignId, $pushObject, $personIds[$pushObject]);
                    $pushPeople      = $personIds[$pushObject];
                }

                if (empty($campaignMembers) && !empty($personIds['Lead'])) {
                    $pushObject      = 'Lead';
                    $campaignMembers = $this->getApiHelper()->checkCampaignMembership($campaignId, $pushObject, $personIds[$pushObject]);
                    $pushPeople      = $personIds[$pushObject];
                }
            } // pushLead should have handled this

            foreach ($pushPeople as $memberId) {
                $campaignMappingId = '-'.$campaignId;

                if (isset($campaignMembers[$memberId])) {
                    $existingCampaignMember = $integrationEntityRepo->getIntegrationsEntityId(
                        'Salesforce',
                        'CampaignMember',
                        'lead',
                        null,
                        null,
                        null,
                        false,
                        0,
                        0,
                        [$campaignMembers[$memberId]]
                    );
                    if ($existingCampaignMember) {
                        foreach ($existingCampaignMember as $member) {
                            $integrationEntity = $integrationEntityRepo->getEntity($member['id']);
                            $referenceId       = $integrationEntity->getId();
                            $internalLeadId    = $integrationEntity->getInternalEntityId();
                        }
                    }
                    $id = !empty($lead->getId()) ? $lead->getId() : '';
                    $id .= '-CampaignMember'.$campaignMembers[$memberId];
                    $id .= !empty($referenceId) ? '-'.$referenceId : '';
                    $id .= $campaignMappingId;
                    $patchurl        = $url.'/'.$campaignMembers[$memberId];
                    $milexData[$id] = [
                        'method'      => 'PATCH',
                        'url'         => $patchurl,
                        'referenceId' => $id,
                        'body'        => $body,
                        'httpHeaders' => [
                            'Sforce-Auto-Assign' => 'FALSE',
                        ],
                    ];
                } else {
                    $id              = (!empty($lead->getId()) ? $lead->getId() : '').'-CampaignMemberNew-null'.$campaignMappingId;
                    $milexData[$id] = [
                        'method'      => 'POST',
                        'url'         => $url,
                        'referenceId' => $id,
                        'body'        => array_merge(
                            $body,
                            [
                                'CampaignId'      => $campaignId,
                                "{$pushObject}Id" => $memberId,
                            ]
                        ),
                    ];
                }
            }

            $request['allOrNone']        = 'false';
            $request['compositeRequest'] = array_values($milexData);

            $this->logger->debug('SALESFORCE: pushLeadToCampaign '.var_export($request, true));

            if (!empty($request)) {
                $result = $this->getApiHelper()->syncMilexToSalesforce($request);

                return (bool) array_sum($this->processCompositeResponse($result['compositeResponse']));
            }
        }

        return false;
    }

    /**
     * @param $email
     *
     * @return mixed|string
     */
    protected function getSyncKey($email)
    {
        return mb_strtolower($this->cleanPushData($email));
    }

    /**
     * @param $checkEmailsInSF
     * @param $milexLeadFieldString
     * @param $sfObject
     * @param $trackedContacts
     * @param $limit
     * @param $fromDate
     * @param $toDate
     * @param $totalCount
     *
     * @return bool
     */
    protected function getMilexContactsToUpdate(
        &$checkEmailsInSF,
        $milexLeadFieldString,
        &$sfObject,
        &$trackedContacts,
        $limit,
        $fromDate,
        $toDate,
        &$totalCount
    ) {
        // Fetch them separately so we can determine if Leads are already Contacts
        $toUpdate = $this->getIntegrationEntityRepository()->findLeadsToUpdate(
            'Salesforce',
            'lead',
            $milexLeadFieldString,
            $limit,
            $fromDate,
            $toDate,
            $sfObject
        )[$sfObject];

        $toUpdateCount = count($toUpdate);
        $totalCount -= $toUpdateCount;

        foreach ($toUpdate as $lead) {
            if (!empty($lead['email'])) {
                $lead                                               = $this->getCompoundMilexFields($lead);
                $key                                                = $this->getSyncKey($lead['email']);
                $trackedContacts[$lead['integration_entity']][$key] = $lead['id'];

                if ('Contact' == $sfObject) {
                    $this->setContactToSync($checkEmailsInSF, $lead);
                } elseif (isset($trackedContacts['Contact'][$key])) {
                    // We already know this is a converted contact so just ignore it
                    $integrationEntity = $this->em->getReference(
                        'MilexPluginBundle:IntegrationEntity',
                        $lead['id']
                    );
                    $this->deleteIntegrationEntities[] = $integrationEntity;
                    $this->logger->debug('SALESFORCE: Converted lead '.$lead['email']);
                } else {
                    $this->setContactToSync($checkEmailsInSF, $lead);
                }
            }
        }

        return 0 === $toUpdateCount;
    }

    /**
     * @param      $fieldMapping
     * @param      $milexLeadFieldString
     * @param      $limit
     * @param      $fromDate
     * @param      $toDate
     * @param null $progress
     *
     * @return array
     *
     * @throws ApiErrorException
     */
    protected function getMilexContactsToCreate(
        &$checkEmailsInSF,
        $fieldMapping,
        $milexLeadFieldString,
        $limit,
        $fromDate,
        $toDate,
        &$totalCount,
        $progress = null
    ) {
        $integrationEntityRepo = $this->getIntegrationEntityRepository();
        $leadsToCreate         = $integrationEntityRepo->findLeadsToCreate(
            'Salesforce',
            $milexLeadFieldString,
            $limit,
            $fromDate,
            $toDate
        );
        $totalCount -= count($leadsToCreate);
        $foundContacts   = [];
        $sfEntityRecords = [
            'totalSize' => 0,
            'records'   => [],
        ];
        $error = false;

        foreach ($leadsToCreate as $lead) {
            $lead = $this->getCompoundMilexFields($lead);

            if (isset($lead['email'])) {
                $this->setContactToSync($checkEmailsInSF, $lead);
            } elseif ($progress) {
                $progress->advance();
            }
        }

        // When creating, we have to check for Contacts first then Lead
        if (isset($fieldMapping['Contact'])) {
            $sfEntityRecords = $this->getSalesforceObjectsByEmails('Contact', $checkEmailsInSF, implode(',', array_keys($fieldMapping['Contact']['create'])));
            if (isset($sfEntityRecords['records'])) {
                foreach ($sfEntityRecords['records'] as $sfContactRecord) {
                    if (!isset($sfContactRecord['Email'])) {
                        continue;
                    }
                    $key                 = $this->getSyncKey($sfContactRecord['Email']);
                    $foundContacts[$key] = $key;
                }
            } else {
                $error = json_encode($sfEntityRecords);
            }
        }

        // For any Milex contacts left over, check to see if existing Leads exist
        if (isset($fieldMapping['Lead']) && $checkSfLeads = array_diff_key($checkEmailsInSF, $foundContacts)) {
            $sfLeadRecords = $this->getSalesforceObjectsByEmails('Lead', $checkSfLeads, implode(',', array_keys($fieldMapping['Lead']['create'])));

            if (isset($sfLeadRecords['records'])) {
                // Merge contact records with these
                $sfEntityRecords['records']   = array_merge($sfEntityRecords['records'], $sfLeadRecords['records']);
                $sfEntityRecords['totalSize'] = (int) $sfEntityRecords['totalSize'] + (int) $sfLeadRecords['totalSize'];
            } else {
                $error = json_encode($sfLeadRecords);
            }
        }

        if ($error) {
            throw new ApiErrorException($error);
        }

        unset($leadsToCreate, $checkSfLeads);

        return $sfEntityRecords;
    }

    /**
     * @param      $objectFields
     * @param      $object
     * @param null $objectId
     * @param null $sfRecord
     *
     * @return array
     */
    protected function buildCompositeBody(
        &$milexData,
        $objectFields,
        $object,
        &$entity,
        $objectId = null,
        $sfRecord = null
    ) {
        $body         = [];
        $updateEntity = [];
        $company      = null;
        $config       = $this->mergeConfigToFeatureSettings([]);

        if ((isset($entity['email']) && !empty($entity['email'])) || (isset($entity['companyname']) && !empty($entity['companyname']))) {
            //use a composite patch here that can update and create (one query) every 200 records
            if (isset($objectFields['update'])) {
                $fields = ($objectId) ? $objectFields['update'] : $objectFields['create'];
                if (isset($entity['company']) && isset($entity['integration_entity']) && 'Contact' == $object) {
                    $accountId = $this->getCompanyName($entity['company'], 'Id', 'Name');

                    if (!$accountId) {
                        //company was not found so create a new company in Salesforce
                        $lead = $this->leadModel->getEntity($entity['internal_entity_id']);
                        if ($lead) {
                            $companies = $this->leadModel->getCompanies($lead);
                            if (!empty($companies)) {
                                foreach ($companies as $companyData) {
                                    if ($companyData['is_primary']) {
                                        $company = $this->companyModel->getEntity($companyData['company_id']);
                                    }
                                }
                                if ($company) {
                                    $sfCompany = $this->pushCompany($company);
                                    if (!empty($sfCompany)) {
                                        $entity['company'] = key($sfCompany);
                                    }
                                }
                            } else {
                                unset($entity['company']);
                            }
                        }
                    } else {
                        $entity['company'] = $accountId;
                    }
                }
                $fields = $this->getBlankFieldsToUpdate($fields, $sfRecord, $objectFields, $config);
            } else {
                $fields = $objectFields;
            }

            foreach ($fields as $sfField => $milexField) {
                if (isset($entity[$milexField])) {
                    $fieldType = (isset($objectFields['types']) && isset($objectFields['types'][$sfField])) ? $objectFields['types'][$sfField]
                        : 'string';
                    if (!empty($entity[$milexField]) and 'boolean' != $fieldType) {
                        $body[$sfField] = $this->cleanPushData($entity[$milexField], $fieldType);
                    } elseif ('boolean' == $fieldType) {
                        $body[$sfField] = $this->cleanPushData($entity[$milexField], $fieldType);
                    }
                }
                if (array_key_exists($sfField, $objectFields['required']['fields']) && empty($body[$sfField])) {
                    if (isset($sfRecord[$sfField])) {
                        $body[$sfField] = $sfRecord[$sfField];
                        if (empty($entity[$milexField]) && !empty($sfRecord[$sfField])
                            && $sfRecord[$sfField] !== $this->translator->trans(
                                'milex.integration.form.lead.unknown'
                            )
                        ) {
                            $updateEntity[$milexField] = $sfRecord[$sfField];
                        }
                    } else {
                        $body[$sfField] = $this->translator->trans('milex.integration.form.lead.unknown');
                    }
                }
            }

            $this->amendLeadDataBeforePush($body);

            if (!empty($body)) {
                $url = '/services/data/v38.0/sobjects/'.$object;
                if ($objectId) {
                    $url .= '/'.$objectId;
                }
                $id              = $entity['internal_entity_id'].'-'.$object.(!empty($entity['id']) ? '-'.$entity['id'] : '');
                $method          = ($objectId) ? 'PATCH' : 'POST';
                $milexData[$id] = [
                    'method'      => $method,
                    'url'         => $url,
                    'referenceId' => $id,
                    'body'        => $body,
                    'httpHeaders' => [
                        'Sforce-Auto-Assign' => ($objectId) ? 'FALSE' : 'TRUE',
                    ],
                ];
            }
        }

        return $updateEntity;
    }

    /**
     * @param $object
     *
     * @return array
     */
    protected function getRequiredFieldString(array $config, array $availableFields, $object)
    {
        $requiredFields = $this->getRequiredFields($availableFields[$object]);

        if ('company' != $object) {
            $requiredFields = $this->prepareFieldsForSync($config['leadFields'], array_keys($requiredFields), $object);
        }

        $requiredString = implode(',', array_keys($requiredFields));

        return [$requiredFields, $requiredString];
    }

    /**
     * @param $config
     *
     * @return array
     */
    protected function prepareFieldsForPush($config)
    {
        $leadFields = array_unique(array_values($config['leadFields']));
        $leadFields = array_combine($leadFields, $leadFields);
        unset($leadFields['milexContactTimelineLink']);
        unset($leadFields['milexContactIsContactableByEmail']);

        $fieldsToUpdateInSf = $this->getPriorityFieldsForIntegration($config);
        $fieldKeys          = array_keys($config['leadFields']);
        $supportedObjects   = [];
        $objectFields       = [];

        // Important to have contacts first!!
        if (false !== array_search('Contact', $config['objects'])) {
            $supportedObjects['Contact'] = 'Contact';
            $fieldsToCreate              = $this->prepareFieldsForSync($config['leadFields'], $fieldKeys, 'Contact');
            $objectFields['Contact']     = [
                'update' => isset($fieldsToUpdateInSf['Contact']) ? array_intersect_key($fieldsToCreate, $fieldsToUpdateInSf['Contact']) : [],
                'create' => $fieldsToCreate,
            ];
        }
        if (false !== array_search('Lead', $config['objects'])) {
            $supportedObjects['Lead'] = 'Lead';
            $fieldsToCreate           = $this->prepareFieldsForSync($config['leadFields'], $fieldKeys, 'Lead');
            $objectFields['Lead']     = [
                'update' => isset($fieldsToUpdateInSf['Lead']) ? array_intersect_key($fieldsToCreate, $fieldsToUpdateInSf['Lead']) : [],
                'create' => $fieldsToCreate,
            ];
        }

        $milexLeadFieldString = implode(', l.', $leadFields);
        $milexLeadFieldString = 'l.'.$milexLeadFieldString;
        $availableFields       = $this->getAvailableLeadFields(['feature_settings' => ['objects' => $supportedObjects]]);

        // Setup required fields and field types
        foreach ($supportedObjects as $object) {
            $objectFields[$object]['types'] = [];
            if (isset($availableFields[$object])) {
                $fieldData = $this->prepareFieldsForSync($availableFields[$object], array_keys($availableFields[$object]), $object);
                foreach ($fieldData as $fieldName => $field) {
                    $objectFields[$object]['types'][$fieldName] = (isset($field['type'])) ? $field['type'] : 'string';
                }
            }

            list($fields, $string) = $this->getRequiredFieldString(
                $config,
                $availableFields,
                $object
            );

            $objectFields[$object]['required'] = [
                'fields' => $fields,
                'string' => $string,
            ];
        }

        return [$objectFields, $milexLeadFieldString, $supportedObjects];
    }

    /**
     * @param        $config
     * @param null   $object
     * @param string $priorityObject
     *
     * @return mixed
     */
    protected function getPriorityFieldsForMilex($config, $object = null, $priorityObject = 'milex')
    {
        $fields = parent::getPriorityFieldsForMilex($config, $object, $priorityObject);

        return ($object && isset($fields[$object])) ? $fields[$object] : $fields;
    }

    /**
     * @param        $config
     * @param null   $object
     * @param string $priorityObject
     *
     * @return mixed
     */
    protected function getPriorityFieldsForIntegration($config, $object = null, $priorityObject = 'milex')
    {
        $fields = parent::getPriorityFieldsForIntegration($config, $object, $priorityObject);
        unset($fields['Contact']['Id'], $fields['Lead']['Id']);

        return ($object && isset($fields[$object])) ? $fields[$object] : $fields;
    }

    /**
     * @param     $response
     * @param int $totalUpdated
     * @param int $totalCreated
     * @param int $totalErrored
     *
     * @return array
     */
    protected function processCompositeResponse($response, &$totalUpdated = 0, &$totalCreated = 0, &$totalErrored = 0)
    {
        if (is_array($response)) {
            foreach ($response as $item) {
                $contactId      = $integrationEntityId      = $campaignId      = null;
                $object         = 'Lead';
                $internalObject = 'lead';
                if (!empty($item['referenceId'])) {
                    $reference = explode('-', $item['referenceId']);
                    if (3 === count($reference)) {
                        list($contactId, $object, $integrationEntityId) = $reference;
                    } elseif (4 === count($reference)) {
                        list($contactId, $object, $integrationEntityId, $campaignId) = $reference;
                    } else {
                        list($contactId, $object) = $reference;
                    }
                }
                if (strstr($object, 'CampaignMember')) {
                    $object = 'CampaignMember';
                }
                if ('Account' == $object) {
                    $internalObject = 'company';
                }
                if (isset($item['body'][0]['errorCode'])) {
                    $exception = new ApiErrorException($item['body'][0]['message']);
                    if ('Contact' == $object || $object = 'Lead') {
                        $exception->setContactId($contactId);
                    }
                    $this->logIntegrationError($exception);
                    $integrationEntity = null;
                    if ($integrationEntityId && 'CampaignMember' !== $object) {
                        $integrationEntity = $this->integrationEntityModel->getEntityByIdAndSetSyncDate($integrationEntityId, new \DateTime());
                    } elseif (isset($campaignId)) {
                        $integrationEntity = $this->integrationEntityModel->getEntityByIdAndSetSyncDate($campaignId, $this->getLastSyncDate());
                    } elseif ($contactId) {
                        $integrationEntity = $this->createIntegrationEntity(
                            $object,
                            null,
                            $internalObject.'-error',
                            $contactId,
                            null,
                            false
                        );
                    }

                    if ($integrationEntity) {
                        $integrationEntity->setInternalEntity('ENTITY_IS_DELETED' === $item['body'][0]['errorCode'] ? $internalObject.'-deleted' : $internalObject.'-error')
                            ->setInternal(['error' => $item['body'][0]['message']]);
                        $this->persistIntegrationEntities[] = $integrationEntity;
                    }
                    ++$totalErrored;
                } elseif (!empty($item['body']['success'])) {
                    if (201 === $item['httpStatusCode']) {
                        // New object created
                        if ('CampaignMember' === $object) {
                            $internal = ['Id' => $item['body']['id']];
                        } else {
                            $internal = [];
                        }
                        $this->salesforceIdMapping[$contactId] = $item['body']['id'];
                        $this->persistIntegrationEntities[]    = $this->createIntegrationEntity(
                            $object,
                            $this->salesforceIdMapping[$contactId],
                            $internalObject,
                            $contactId,
                            $internal,
                            false
                        );
                    }
                    ++$totalCreated;
                } elseif (204 === $item['httpStatusCode']) {
                    // Record was updated
                    if ($integrationEntityId) {
                        $integrationEntity = $this->integrationEntityModel->getEntityByIdAndSetSyncDate($integrationEntityId, $this->getLastSyncDate());
                        if ($integrationEntity) {
                            if (isset($this->salesforceIdMapping[$contactId])) {
                                $integrationEntity->setIntegrationEntityId($this->salesforceIdMapping[$contactId]);
                            }

                            $this->persistIntegrationEntities[] = $integrationEntity;
                        }
                    } elseif (!empty($this->salesforceIdMapping[$contactId])) {
                        // Found in Salesforce so create a new record for it
                        $this->persistIntegrationEntities[] = $this->createIntegrationEntity(
                            $object,
                            $this->salesforceIdMapping[$contactId],
                            $internalObject,
                            $contactId,
                            [],
                            false
                        );
                    }

                    ++$totalUpdated;
                } else {
                    $error = 'http status code '.$item['httpStatusCode'];
                    switch (true) {
                        case !empty($item['body'][0]['message']['message']):
                            $error = $item['body'][0]['message']['message'];
                            break;
                        case !empty($item['body']['message']):
                            $error = $item['body']['message'];
                            break;
                    }

                    $exception = new ApiErrorException($error);
                    if (!empty($item['referenceId']) && ('Contact' == $object || $object = 'Lead')) {
                        $exception->setContactId($item['referenceId']);
                    }
                    $this->logIntegrationError($exception);
                    ++$totalErrored;

                    if ($integrationEntityId) {
                        $integrationEntity = $this->integrationEntityModel->getEntityByIdAndSetSyncDate($integrationEntityId, $this->getLastSyncDate());
                        if ($integrationEntity) {
                            if (isset($this->salesforceIdMapping[$contactId])) {
                                $integrationEntity->setIntegrationEntityId($this->salesforceIdMapping[$contactId]);
                            }

                            $this->persistIntegrationEntities[] = $integrationEntity;
                        }
                    } elseif (!empty($this->salesforceIdMapping[$contactId])) {
                        // Found in Salesforce so create a new record for it
                        $this->persistIntegrationEntities[] = $this->createIntegrationEntity(
                            $object,
                            $this->salesforceIdMapping[$contactId],
                            $internalObject,
                            $contactId,
                            [],
                            false
                        );
                    }
                }
            }
        }

        $this->cleanupFromSync();

        return [$totalUpdated, $totalCreated];
    }

    /**
     * @param $sfObject
     * @param $checkEmailsInSF
     * @param $requiredFieldString
     *
     * @return array
     */
    protected function getSalesforceObjectsByEmails($sfObject, $checkEmailsInSF, $requiredFieldString)
    {
        // Salesforce craps out with double quotes and unescaped single quotes
        $findEmailsInSF = array_map(
            function ($lead) {
                return str_replace("'", "\'", $this->cleanPushData($lead['email']));
            },
            $checkEmailsInSF
        );

        $fieldString = "'".implode("','", $findEmailsInSF)."'";
        $queryUrl    = $this->getQueryUrl();
        $findQuery   = ('Lead' === $sfObject)
            ?
            'select Id, '.$requiredFieldString.', ConvertedContactId from Lead where isDeleted = false and Email in ('.$fieldString.')'
            :
            'select Id, '.$requiredFieldString.' from Contact where isDeleted = false and Email in ('.$fieldString.')';

        return $this->getApiHelper()->request('query', ['q' => $findQuery], 'GET', false, null, $queryUrl);
    }

    /**
     * @param      $objectFields
     * @param      $milexLeadFieldString
     * @param      $sfEntityRecords
     * @param null $progress
     */
    protected function prepareMilexContactsToUpdate(
        &$milexData,
        &$checkEmailsInSF,
        &$processedLeads,
        &$trackedContacts,
        &$leadsToSync,
        $objectFields,
        $milexLeadFieldString,
        $sfEntityRecords,
        $progress = null
    ) {
        foreach ($sfEntityRecords['records'] as $sfKey => $sfEntityRecord) {
            $skipObject = false;
            $syncLead   = false;
            $sfObject   = $sfEntityRecord['attributes']['type'];
            if (!isset($sfEntityRecord['Email'])) {
                // This is a record we don't recognize so continue
                return;
            }
            $key = $this->getSyncKey($sfEntityRecord['Email']);
            if (!isset($sfEntityRecord['Id']) || (!isset($checkEmailsInSF[$key]) && !isset($processedLeads[$key]))) {
                // This is a record we don't recognize so continue
                return;
            }

            $leadData  = (isset($processedLeads[$key])) ? $processedLeads[$key] : $checkEmailsInSF[$key];
            $contactId = $leadData['internal_entity_id'];

            if (
                isset($checkEmailsInSF[$key])
                && (
                    (
                        'Lead' === $sfObject && !empty($sfEntityRecord['ConvertedContactId'])
                    )
                    || (
                        isset($checkEmailsInSF[$key]['integration_entity']) && 'Contact' === $sfObject
                        && 'Lead' === $checkEmailsInSF[$key]['integration_entity']
                    )
                )
            ) {
                $deleted = false;
                // This is a converted lead so remove the Lead entity leaving the Contact entity
                if (!empty($trackedContacts['Lead'][$key])) {
                    $this->deleteIntegrationEntities[] = $this->em->getReference(
                        'MilexPluginBundle:IntegrationEntity',
                        $trackedContacts['Lead'][$key]
                    );
                    $deleted = true;
                    unset($trackedContacts['Lead'][$key]);
                }

                if ($contactEntity = $this->checkLeadIsContact($trackedContacts['Contact'], $key, $contactId, $milexLeadFieldString)) {
                    // This Lead is already a Contact but was not updated for whatever reason
                    if (!$deleted) {
                        $this->deleteIntegrationEntities[] = $this->em->getReference(
                            'MilexPluginBundle:IntegrationEntity',
                            $checkEmailsInSF[$key]['id']
                        );
                    }

                    // Update the Contact record instead
                    $checkEmailsInSF[$key]            = $contactEntity;
                    $trackedContacts['Contact'][$key] = $contactEntity['id'];
                } else {
                    $id = (!empty($sfEntityRecord['ConvertedContactId'])) ? $sfEntityRecord['ConvertedContactId'] : $sfEntityRecord['Id'];
                    // This contact does not have a Contact record
                    $integrationEntity = $this->createIntegrationEntity(
                        'Contact',
                        $id,
                        'lead',
                        $contactId
                    );

                    $checkEmailsInSF[$key]['integration_entity']    = 'Contact';
                    $checkEmailsInSF[$key]['integration_entity_id'] = $id;
                    $checkEmailsInSF[$key]['id']                    = $integrationEntity;
                }

                $this->logger->debug('SALESFORCE: Converted lead '.$sfEntityRecord['Email']);

                // skip if this is a Lead object since it'll be handled with the Contact entry
                if ('Lead' === $sfObject) {
                    unset($checkEmailsInSF[$key]);
                    unset($sfEntityRecords['records'][$sfKey]);
                    $skipObject = true;
                }
            }

            if (!$skipObject) {
                // Only progress if we have a unique Lead and not updating a Salesforce entry duplicate
                if (!isset($processedLeads[$key])) {
                    if ($progress) {
                        $progress->advance();
                    }

                    // Mark that this lead has been processed
                    $leadData = $processedLeads[$key] = $checkEmailsInSF[$key];
                }

                // Keep track of Milex ID to Salesforce ID for the integration table
                $this->salesforceIdMapping[$contactId] = (!empty($sfEntityRecord['ConvertedContactId'])) ? $sfEntityRecord['ConvertedContactId']
                    : $sfEntityRecord['Id'];

                $leadEntity = $this->em->getReference('MilexLeadBundle:Lead', $leadData['internal_entity_id']);
                if ($updateLead = $this->buildCompositeBody(
                    $milexData,
                    $objectFields[$sfObject],
                    $sfObject,
                    $leadData,
                    $sfEntityRecord['Id'],
                    $sfEntityRecord
                )
                ) {
                    // Get the lead entity
                    /* @var Lead $leadEntity */
                    foreach ($updateLead as $milexField => $sfValue) {
                        $leadEntity->addUpdatedField($milexField, $sfValue);
                    }

                    $syncLead = !empty($leadEntity->getChanges(true));
                }

                // Validate if we have a company for this Milex contact
                if (!empty($sfEntityRecord['Company'])
                    && $sfEntityRecord['Company'] !== $this->translator->trans(
                        'milex.integration.form.lead.unknown'
                    )
                ) {
                    $company = IdentifyCompanyHelper::identifyLeadsCompany(
                        ['company' => $sfEntityRecord['Company']],
                        null,
                        $this->companyModel
                    );

                    if (!empty($company[2])) {
                        $syncLead = $this->companyModel->addLeadToCompany($company[2], $leadEntity);
                        $this->em->detach($company[2]);
                    }
                }

                if ($syncLead) {
                    $leadsToSync[] = $leadEntity;
                } else {
                    $this->em->detach($leadEntity);
                }
            }

            unset($checkEmailsInSF[$key]);
        }
    }

    /**
     * @param $milexData
     * @param $checkEmailsInSF
     * @param $processedLeads
     * @param $objectFields
     */
    protected function prepareMilexContactsToCreate(
        &$milexData,
        &$checkEmailsInSF,
        &$processedLeads,
        $objectFields
    ) {
        foreach ($checkEmailsInSF as $key => $lead) {
            if (!empty($lead['integration_entity_id'])) {
                if ($this->buildCompositeBody(
                    $milexData,
                    $objectFields[$lead['integration_entity']],
                    $lead['integration_entity'],
                    $lead,
                    $lead['integration_entity_id']
                )
                ) {
                    $this->logger->debug('SALESFORCE: Contact has existing ID so updating '.$lead['email']);
                }
            } else {
                $this->buildCompositeBody(
                    $milexData,
                    $objectFields['Lead'],
                    'Lead',
                    $lead
                );
            }

            $processedLeads[$key] = $checkEmailsInSF[$key];
            unset($checkEmailsInSF[$key]);
        }
    }

    /**
     * @param     $milexData
     * @param int $totalUpdated
     * @param int $totalCreated
     * @param int $totalErrored
     */
    protected function makeCompositeRequest($milexData, &$totalUpdated = 0, &$totalCreated = 0, &$totalErrored = 0)
    {
        if (empty($milexData)) {
            return;
        }

        /** @var SalesforceApi $apiHelper */
        $apiHelper = $this->getApiHelper();

        // We can only send 25 at a time
        $request              = [];
        $request['allOrNone'] = 'false';
        $chunked              = array_chunk($milexData, 25);

        foreach ($chunked as $chunk) {
            // We can only submit 25 at a time
            if ($chunk) {
                $request['compositeRequest'] = $chunk;
                $result                      = $apiHelper->syncMilexToSalesforce($request);
                $this->logger->debug('SALESFORCE: Sync Composite  '.var_export($request, true));
                $this->processCompositeResponse($result['compositeResponse'], $totalUpdated, $totalCreated, $totalErrored);
            }
        }
    }

    /**
     * @param $checkEmailsInSF
     * @param $lead
     *
     * @return bool|mixed|string
     */
    protected function setContactToSync(&$checkEmailsInSF, $lead)
    {
        $key = $this->getSyncKey($lead['email']);
        if (isset($checkEmailsInSF[$key])) {
            // this is a duplicate in Milex
            $this->milexDuplicates[$lead['internal_entity_id']] = 'lead-duplicate';

            return false;
        }

        $checkEmailsInSF[$key] = $lead;

        return $key;
    }

    /**
     * @param $currentContactList
     * @param $limit
     *
     * @return int
     */
    protected function getSalesforceSyncLimit($currentContactList, $limit)
    {
        return $limit - count($currentContactList);
    }

    /**
     * @param $trackedContacts
     * @param $email
     * @param $contactId
     * @param $leadFields
     *
     * @return array|bool
     */
    protected function checkLeadIsContact(&$trackedContacts, $email, $contactId, $leadFields)
    {
        if (empty($trackedContacts[$email])) {
            // Check if there's an existing entry
            return $this->getIntegrationEntityRepository()->getIntegrationEntity(
                $this->getName(),
                'Contact',
                'lead',
                $contactId,
                $leadFields
            );
        }

        return false;
    }

    /**
     * @param       $fieldsToUpdate
     * @param array $objects
     *
     * @return array
     */
    protected function cleanPriorityFields($fieldsToUpdate, $objects = null)
    {
        if (null === $objects) {
            $objects = ['Lead', 'Contact'];
        }

        if (isset($fieldsToUpdate['leadFields'])) {
            // Pass in the whole config
            $fields = $fieldsToUpdate;
        } else {
            $fields = array_flip($fieldsToUpdate);
        }

        return $this->prepareFieldsForSync($fields, $fieldsToUpdate, $objects);
    }

    /**
     * @param $config
     *
     * @return array
     */
    protected function mapContactDataForPush(Lead $lead, $config)
    {
        $fields             = array_keys($config['leadFields']);
        $fieldsToUpdateInSf = $this->getPriorityFieldsForIntegration($config);
        $fieldMapping       = [
            'Lead'    => [],
            'Contact' => [],
        ];
        $mappedData = [
            'Lead'    => [],
            'Contact' => [],
        ];

        foreach (['Lead', 'Contact'] as $object) {
            if (isset($config['objects']) && false !== array_search($object, $config['objects'])) {
                $fieldMapping[$object]['create'] = $this->prepareFieldsForSync($config['leadFields'], $fields, $object);
                $fieldMapping[$object]['update'] = isset($fieldsToUpdateInSf[$object]) ? array_intersect_key(
                    $fieldMapping[$object]['create'],
                    $fieldsToUpdateInSf[$object]
                ) : [];

                // Create an update and
                $mappedData[$object]['create'] = $this->populateLeadData(
                    $lead,
                    [
                        'leadFields'       => $fieldMapping[$object]['create'], // map with all fields available
                        'object'           => $object,
                        'feature_settings' => [
                            'objects' => $config['objects'],
                        ],
                    ]
                );

                if (isset($mappedData[$object]['create']['Id'])) {
                    unset($mappedData[$object]['create']['Id']);
                }

                $this->amendLeadDataBeforePush($mappedData[$object]['create']);

                // Set the update fields
                $mappedData[$object]['update'] = array_intersect_key($mappedData[$object]['create'], $fieldMapping[$object]['update']);
            }
        }

        return $mappedData;
    }

    /**
     * @param $config
     *
     * @return array
     */
    protected function mapCompanyDataForPush(Company $company, $config)
    {
        $object     = 'company';
        $entity     = [];
        $mappedData = [
            $object => [],
        ];

        if (isset($config['objects']) && false !== array_search($object, $config['objects'])) {
            $fieldKeys          = array_keys($config['companyFields']);
            $fieldsToCreate     = $this->prepareFieldsForSync($config['companyFields'], $fieldKeys, 'Account');
            $fieldsToUpdateInSf = $this->getPriorityFieldsForIntegration($config, 'Account', 'milex_company');

            $fieldMapping[$object] = [
                'update' => !empty($fieldsToUpdateInSf) ? array_intersect_key($fieldsToCreate, $fieldsToUpdateInSf) : [],
                'create' => $fieldsToCreate,
            ];
            $entity['primaryCompany'] = $company->getProfileFields();

            // Create an update and
            $mappedData[$object]['create'] = $this->populateCompanyData(
                $entity,
                [
                    'companyFields'    => $fieldMapping[$object]['create'], // map with all fields available
                    'object'           => $object,
                    'feature_settings' => [
                        'objects' => $config['objects'],
                    ],
                ]
            );

            if (isset($mappedData[$object]['create']['Id'])) {
                unset($mappedData[$object]['create']['Id']);
            }

            $this->amendLeadDataBeforePush($mappedData[$object]['create']);

            // Set the update fields
            $mappedData[$object]['update'] = array_intersect_key($mappedData[$object]['create'], $fieldMapping[$object]['update']);
        }

        return $mappedData;
    }

    /**
     * @param $mappedData
     */
    public function amendLeadDataBeforePush(&$mappedData)
    {
        // normalize for multiselect field
        foreach ($mappedData as &$data) {
            if (is_string($data)) {
                $data = str_replace('|', ';', $data);
            }
        }

        $mappedData = StateValidationHelper::validate($mappedData);
    }

    /**
     * @param string $object
     *
     * @return array
     */
    public function getFieldsForQuery($object)
    {
        $fields = $this->getIntegrationSettings()->getFeatureSettings();
        switch ($object) {
            case 'company':
            case 'Account':
                $fields = array_keys(array_filter($fields['companyFields']));
                break;
            default:
                $mixedFields = array_filter($fields['leadFields']);
                $fields      = [];
                foreach ($mixedFields as $sfField => $mField) {
                    if (false !== strpos($sfField, '__'.$object)) {
                        $fields[] = str_replace('__'.$object, '', $sfField);
                    }
                    if (false !== strpos($sfField, '-'.$object)) {
                        $fields[] = str_replace('-'.$object, '', $sfField);
                    }
                }
        }

        return $fields;
    }

    /**
     * @param $sfObject
     * @param $sfFieldString
     *
     * @return mixed|string
     *
     * @throws ApiErrorException
     */
    public function getDncHistory($sfObject, $sfFieldString)
    {
        //get last modified date for donot contact in Salesforce
        $historySelect = 'Select Field, '.$sfObject.'Id, CreatedDate, isDeleted, NewValue from '.$sfObject.'History where Field = \'HasOptedOutOfEmail\' and '.$sfObject.'Id IN ('.$sfFieldString.') ORDER BY CreatedDate DESC';
        $queryUrl      = $this->getQueryUrl();

        return $this->getApiHelper()->request('query', ['q' => $historySelect], 'GET', false, null, $queryUrl);
    }

    /**
     * Update the record in each system taking the last modified record.
     *
     * @param string $channel
     * @param string $sfObject
     *
     * @return int
     *
     * @throws ApiErrorException
     */
    public function pushLeadDoNotContactByDate($channel, &$sfRecords, $sfObject, $params = [])
    {
        $filters = [];
        $leadIds = [];

        if (empty($sfRecords) || !isset($sfRecords['milexContactIsContactableByEmail']) && !$this->updateDncByDate()) {
            return;
        }

        foreach ($sfRecords as $record) {
            if (empty($record['integration_entity_id'])) {
                continue;
            }

            $leadIds[$record['internal_entity_id']]    = $record['integration_entity_id'];
            $leadEmails[$record['internal_entity_id']] = $record['email'];
        }

        $sfFieldString = "'".implode("','", $leadIds)."'";

        $historySF = $this->getDncHistory($sfObject, $sfFieldString);
        //if there is no records of when it was modified in SF then just exit
        if (empty($historySF['records'])) {
            return;
        }

        //get last modified date for donot contact in Milex
        $auditLogRepo        = $this->em->getRepository('MilexCoreBundle:AuditLog');
        $filters['search']   = 'dnc_channel_status%'.$channel;
        $lastModifiedDNCDate = $auditLogRepo->getAuditLogsForLeads(array_flip($leadIds), $filters, ['dateAdded', 'DESC'], $params['start']);
        $trackedIds          = [];
        foreach ($historySF['records'] as $sfModifiedDNC) {
            // if we have no history in Milex, then update the Milex record
            if (empty($lastModifiedDNCDate)) {
                $leads  = array_flip($leadIds);
                $leadId = $leads[$sfModifiedDNC[$sfObject.'Id']];
                $this->updateMilexDNC($leadId, $sfModifiedDNC['NewValue']);
                $key = $this->getSyncKey($leadEmails[$leadId]);
                unset($sfRecords[$key]['milexContactIsContactableByEmail']);
                continue;
            }

            foreach ($lastModifiedDNCDate as $logs) {
                $leadId = $logs['objectId'];
                if (strtotime($logs['dateAdded']->format('c')) > strtotime($sfModifiedDNC['CreatedDate'])) {
                    $trackedIds[] = $leadId;
                }
                if (((isset($leadIds[$leadId]) && $leadIds[$leadId] == $sfModifiedDNC[$sfObject.'Id']))
                    && ((strtotime($sfModifiedDNC['CreatedDate']) > strtotime($logs['dateAdded']->format('c')))) && !in_array($leadId, $trackedIds)) {
                    //SF was updated last so update Milex record
                    $key = $this->getSyncKey($leadEmails[$leadId]);
                    unset($sfRecords[$key]['milexContactIsContactableByEmail']);
                    $this->updateMilexDNC($leadId, $sfModifiedDNC['NewValue']);
                    $trackedIds[] = $leadId;
                    break;
                }
            }
        }
    }

    /**
     * @param $leadId
     * @param $newDncValue
     */
    private function updateMilexDNC($leadId, $newDncValue)
    {
        $lead = $this->leadModel->getEntity($leadId);

        if (true == $newDncValue) {
            $this->doNotContact->addDncForContact($lead->getId(), 'email', DoNotContact::MANUAL, 'Set by Salesforce', true, true, true);
        } elseif (false == $newDncValue) {
            $this->doNotContact->removeDncForContact($lead->getId(), 'email', true);
        }
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function pushCompanies($params = [])
    {
        $limit                   = (isset($params['limit'])) ? $params['limit'] : 100;
        list($fromDate, $toDate) = $this->getSyncTimeframeDates($params);
        $config                  = $this->mergeConfigToFeatureSettings($params);
        $integrationEntityRepo   = $this->getIntegrationEntityRepository();

        if (!isset($config['companyFields'])) {
            return [0, 0, 0, 0];
        }

        $totalUpdated = 0;
        $totalCreated = 0;
        $totalErrors  = 0;
        $sfObject     = 'Account';

        //all available fields in Salesforce for Account
        $availableFields = $this->getAvailableLeadFields(['feature_settings' => ['objects' => [$sfObject]]]);

        //get company fields from Milex that have been mapped
        $milexCompanyFieldString = implode(', l.', $config['companyFields']);
        $milexCompanyFieldString = 'l.'.$milexCompanyFieldString;

        $fieldKeys          = array_keys($config['companyFields']);
        $fieldsToCreate     = $this->prepareFieldsForSync($config['companyFields'], $fieldKeys, $sfObject);
        $fieldsToUpdateInSf = $this->getPriorityFieldsForIntegration($config, $sfObject, 'milex_company');

        $objectFields['company'] = [
            'update' => !empty($fieldsToUpdateInSf) ? array_intersect_key($fieldsToCreate, $fieldsToUpdateInSf) : [],
            'create' => $fieldsToCreate,
        ];

        list($fields, $string) = $this->getRequiredFieldString(
            $config,
            $availableFields,
            'company'
        );

        $objectFields['company']['required'] = [
            'fields' => $fields,
            'string' => $string,
        ];

        if (empty($objectFields)) {
            return [0, 0, 0, 0];
        }

        $originalLimit = $limit;
        $progress      = false;

        // Get a total number of companies to be updated and/or created for the progress counter
        $totalToUpdate = array_sum(
            $integrationEntityRepo->findLeadsToUpdate(
                'Salesforce',
                'company',
                $milexCompanyFieldString,
                false,
                $fromDate,
                $toDate,
                $sfObject,
                []
            )
        );
        $totalToCreate = $integrationEntityRepo->findLeadsToCreate(
            'Salesforce',
            $milexCompanyFieldString,
            false,
            $fromDate,
            $toDate,
            'company'
        );

        $totalCount = $totalToProcess = $totalToCreate + $totalToUpdate;

        if (defined('IN_MAUTIC_CONSOLE')) {
            // start with update
            if ($totalToUpdate + $totalToCreate) {
                $output = new ConsoleOutput();
                $output->writeln("About $totalToUpdate to update and about $totalToCreate to create/update");
                $progress = new ProgressBar($output, $totalCount);
            }
        }

        $noMoreUpdates = false;

        while ($totalCount > 0) {
            $limit              = $originalLimit;
            $milexData         = [];
            $checkCompaniesInSF = [];
            $companiesToSync    = [];
            $processedCompanies = [];

            // Process the updates
            if (!$noMoreUpdates) {
                $noMoreUpdates = $this->getMilexRecordsToUpdate(
                    $checkCompaniesInSF,
                    $milexCompanyFieldString,
                    $sfObject,
                    $limit,
                    $fromDate,
                    $toDate,
                    $totalCount,
                    'company'
                );

                if ($limit) {
                    // Mainly done for test mocking purposes
                    $limit = $this->getSalesforceSyncLimit($checkCompaniesInSF, $limit);
                }
            }

            // If there is still room - grab Milex companies to create if the Lead object is enabled
            $sfEntityRecords = [];
            if ((null === $limit || $limit > 0) && !empty($milexCompanyFieldString)) {
                $this->getMilexEntitesToCreate(
                    $checkCompaniesInSF,
                    $milexCompanyFieldString,
                    $limit,
                    $fromDate,
                    $toDate,
                    $totalCount,
                    $progress
                );
            }

            if ($checkCompaniesInSF) {
                $sfEntityRecords = $this->getSalesforceAccountsByName($checkCompaniesInSF, implode(',', array_keys($config['companyFields'])));

                if (!isset($sfEntityRecords['records'])) {
                    // Something is wrong so throw an exception to prevent creating a bunch of new companies
                    $this->cleanupFromSync(
                        $companiesToSync,
                        json_encode($sfEntityRecords)
                    );
                }
            }

            // We're done
            if (!$checkCompaniesInSF) {
                break;
            }

            if (!empty($sfEntityRecords) and isset($sfEntityRecords['records'])) {
                $this->prepareMilexCompaniesToUpdate(
                    $milexData,
                    $checkCompaniesInSF,
                    $processedCompanies,
                    $companiesToSync,
                    $objectFields,
                    $sfEntityRecords,
                    $progress
                );
            }

            // Only create left over if Lead object is enabled in integration settings
            if ($checkCompaniesInSF) {
                $this->prepareMilexCompaniesToCreate(
                    $milexData,
                    $checkCompaniesInSF,
                    $processedCompanies,
                    $objectFields
                );
            }

            // Persist pending changes
            $this->cleanupFromSync($companiesToSync);

            $this->makeCompositeRequest($milexData, $totalUpdated, $totalCreated, $totalErrors);

            // Stop gap - if 100% let's kill the script
            if ($progress && $progress->getProgressPercent() >= 1) {
                break;
            }
        }

        if ($progress) {
            $progress->finish();
            $output->writeln('');
        }

        $this->logger->debug('SALESFORCE: '.$this->getApiHelper()->getRequestCounter().' API requests made for pushCompanies');

        // Assume that those not touched are ignored due to not having matching fields, duplicates, etc
        $totalIgnored = $totalToProcess - ($totalUpdated + $totalCreated + $totalErrors);

        if ($totalIgnored < 0) { //this could have been marked as deleted so it was not pushed
            $totalIgnored = $totalIgnored * -1;
        }

        return [$totalUpdated, $totalCreated, $totalErrors, $totalIgnored];
    }

    /**
     * @param      $objectFields
     * @param      $sfEntityRecords
     * @param null $progress
     */
    protected function prepareMilexCompaniesToUpdate(
        &$milexData,
        &$checkCompaniesInSF,
        &$processedCompanies,
        &$companiesToSync,
        $objectFields,
        $sfEntityRecords,
        $progress = null
    ) {
        foreach ($sfEntityRecords['records'] as $sfEntityRecord) {
            $syncCompany = false;
            $update      = false;
            $sfObject    = $sfEntityRecord['attributes']['type'];
            if (!isset($sfEntityRecord['Name'])) {
                // This is a record we don't recognize so continue
                return;
            }
            $key = $sfEntityRecord['Id'];

            if (!isset($sfEntityRecord['Id'])) {
                // This is a record we don't recognize so continue
                return;
            }

            $id = $sfEntityRecord['Id'];
            if (isset($checkCompaniesInSF[$key])) {
                $companyData = (isset($processedCompanies[$key])) ? $processedCompanies[$key] : $checkCompaniesInSF[$key];
                $update      = true;
            } else {
                foreach ($checkCompaniesInSF as $milexKey => $milexCompanies) {
                    $key = $milexKey;

                    if (isset($milexCompanies['companyname']) && $milexCompanies['companyname'] == $sfEntityRecord['Name']) {
                        $companyData = (isset($processedCompanies[$key])) ? $processedCompanies[$key] : $checkCompaniesInSF[$key];
                        $companyId   = $companyData['internal_entity_id'];

                        $integrationEntity = $this->createIntegrationEntity(
                            $sfObject,
                            $id,
                            'company',
                            $companyId
                        );

                        $checkCompaniesInSF[$key]['integration_entity']    = $sfObject;
                        $checkCompaniesInSF[$key]['integration_entity_id'] = $id;
                        $checkCompaniesInSF[$key]['id']                    = $integrationEntity->getId();
                        $update                                            = true;
                    }
                }
            }

            if (!$update) {
                return;
            }

            if (!isset($processedCompanies[$key])) {
                if ($progress) {
                    $progress->advance();
                }
                // Mark that this lead has been processed
                $companyData = $processedCompanies[$key] = $checkCompaniesInSF[$key];
            }

            $companyEntity = $this->em->getReference('MilexLeadBundle:Company', $companyData['internal_entity_id']);

            if ($updateCompany = $this->buildCompositeBody(
                $milexData,
                $objectFields['company'],
                $sfObject,
                $companyData,
                $sfEntityRecord['Id'],
                $sfEntityRecord
            )
            ) {
                // Get the company entity
                /* @var Lead $leadEntity */
                foreach ($updateCompany as $milexField => $sfValue) {
                    $companyEntity->addUpdatedField($milexField, $sfValue);
                }

                $syncCompany = !empty($companyEntity->getChanges(true));
            }
            if ($syncCompany) {
                $companiesToSync[] = $companyEntity;
            } else {
                $this->em->detach($companyEntity);
            }

            unset($checkCompaniesInSF[$key]);
        }
    }

    /**
     * @param $milexData
     * @param $processedCompanies
     * @param $objectFields
     */
    protected function prepareMilexCompaniesToCreate(
        &$milexData,
        &$checkCompaniesInSF,
        &$processedCompanies,
        $objectFields
    ) {
        foreach ($checkCompaniesInSF as $key => $company) {
            if (!empty($company['integration_entity_id']) and array_key_exists($key, $processedCompanies)) {
                if ($this->buildCompositeBody(
                    $milexData,
                    $objectFields['company'],
                    $company['integration_entity'],
                    $company,
                    $company['integration_entity_id']
                )
                ) {
                    $this->logger->debug('SALESFORCE: Company has existing ID so updating '.$company['integration_entity_id']);
                }
            } else {
                $this->buildCompositeBody(
                    $milexData,
                    $objectFields['company'],
                    'Account',
                    $company
                );
            }

            $processedCompanies[$key] = $checkCompaniesInSF[$key];
            unset($checkCompaniesInSF[$key]);
        }
    }

    /**
     * @param $sfObject
     * @param $limit
     * @param $fromDate
     * @param $toDate
     * @param $totalCount
     *
     * @return bool
     */
    protected function getMilexRecordsToUpdate(
        &$checkIdsInSF,
        $milexEntityFieldString,
        &$sfObject,
        $limit,
        $fromDate,
        $toDate,
        &$totalCount,
        $internalEntity
    ) {
        // Fetch them separately so we can determine if Leads are already Contacts
        $toUpdate = $this->getIntegrationEntityRepository()->findLeadsToUpdate(
            'Salesforce',
            $internalEntity,
            $milexEntityFieldString,
            $limit,
            $fromDate,
            $toDate,
            $sfObject
        )[$sfObject];

        $toUpdateCount = count($toUpdate);
        $totalCount -= $toUpdateCount;

        foreach ($toUpdate as $entity) {
            if (!empty($entity['integration_entity_id'])) {
                $checkIdsInSF[$entity['integration_entity_id']] = $entity;
            }
        }

        return 0 === $toUpdateCount;
    }

    /**
     * @param      $milexCompanyFieldString
     * @param      $limit
     * @param      $fromDate
     * @param      $toDate
     * @param null $progress
     */
    protected function getMilexEntitesToCreate(
        &$checkIdsInSF,
        $milexCompanyFieldString,
        $limit,
        $fromDate,
        $toDate,
        &$totalCount,
        $progress = null
    ) {
        $integrationEntityRepo = $this->getIntegrationEntityRepository();
        $entitiesToCreate      = $integrationEntityRepo->findLeadsToCreate(
            'Salesforce',
            $milexCompanyFieldString,
            $limit,
            $fromDate,
            $toDate,
            'company'
        );
        $totalCount -= count($entitiesToCreate);

        foreach ($entitiesToCreate as $entity) {
            if (isset($entity['companyname'])) {
                $checkIdsInSF[$entity['internal_entity_id']] = $entity;
            } elseif ($progress) {
                $progress->advance();
            }
        }
    }

    /**
     * @param $checkIdsInSF
     * @param $requiredFieldString
     *
     * @return array
     *
     * @throws ApiErrorException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    protected function getSalesforceAccountsByName(&$checkIdsInSF, $requiredFieldString)
    {
        $searchForIds   = [];
        $searchForNames = [];

        foreach ($checkIdsInSF as $key => $company) {
            if (!empty($company['integration_entity_id'])) {
                $searchForIds[$key] = $company['integration_entity_id'];

                continue;
            }

            if (!empty($company['companyname'])) {
                $searchForNames[$key] = $company['companyname'];
            }
        }

        $resultsByName = $this->getApiHelper()->getCompaniesByName($searchForNames, $requiredFieldString);
        $resultsById   = [];
        if (!empty($searchForIds)) {
            $resultsById = $this->getApiHelper()->getCompaniesById($searchForIds, $requiredFieldString);

            //mark as deleleted
            foreach ($resultsById['records'] as $sfId => $record) {
                if (isset($record['IsDeleted']) && 1 == $record['IsDeleted']) {
                    if ($foundKey = array_search($record['Id'], $searchForIds)) {
                        $integrationEntity = $this->em->getReference('MilexPluginBundle:IntegrationEntity', $checkIdsInSF[$foundKey]['id']);
                        $integrationEntity->setInternalEntity('company-deleted');
                        $this->persistIntegrationEntities[] = $integrationEntity;
                        unset($checkIdsInSF[$foundKey]);
                    }

                    unset($resultsById['records'][$sfId]);
                }
            }
        }

        $this->cleanupFromSync();

        return array_merge($resultsByName, $resultsById);
    }

    public function getCompanyName($accountId, $field, $searchBy = 'Id')
    {
        $companyField   = null;
        $accountId      = str_replace("'", "\'", $this->cleanPushData($accountId));
        $companyQuery   = 'Select Id, Name from Account where '.$searchBy.' = \''.$accountId.'\' and IsDeleted = false';
        $contactCompany = $this->getApiHelper()->getLeads($companyQuery, 'Account');

        if (!empty($contactCompany['records'])) {
            foreach ($contactCompany['records'] as $company) {
                if (!empty($company[$field])) {
                    $companyField = $company[$field];
                    break;
                }
            }
        }

        return $companyField;
    }

    public function getLeadDoNotContactByDate($channel, $matchedFields, $object, $lead, $sfData, $params = [])
    {
        if (isset($matchedFields['milexContactIsContactableByEmail']) and true === $this->updateDncByDate()) {
            $matchedFields['internal_entity_id']    = $lead->getId();
            $matchedFields['integration_entity_id'] = $sfData['Id__'.$object];
            $record[$lead->getEmail()]              = $matchedFields;
            $this->pushLeadDoNotContactByDate($channel, $record, $object, $params);

            return $record[$lead->getEmail()];
        }

        return $matchedFields;
    }
}