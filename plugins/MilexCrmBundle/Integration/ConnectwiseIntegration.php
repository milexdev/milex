<?php

namespace MilexPlugin\MilexCrmBundle\Integration;

use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Milex\LeadBundle\Entity\Lead;
use Milex\PluginBundle\Entity\IntegrationEntity;
use Milex\PluginBundle\Entity\IntegrationEntityRepository;
use Milex\PluginBundle\Exception\ApiErrorException;
use Milex\PluginBundle\Integration\IntegrationObject;
use MilexPlugin\MilexCrmBundle\Form\Type\IntegrationCampaignsTaskType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilder;

/**
 * @method \MilexPlugin\MilexCrmBundle\Api\ConnectwiseApi getApiHelper
 */
class ConnectwiseIntegration extends CrmAbstractIntegration
{
    const PAGESIZE = 200;

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'Connectwise';
    }

    /**
     * @return array
     */
    public function getSupportedFeatures()
    {
        return ['push_lead', 'get_leads'];
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getRequiredKeyFields()
    {
        return [
            'username'  => 'milex.connectwise.form.integrator',
            'password'  => 'milex.connectwise.form.privatekey',
            'site'      => 'milex.connectwise.form.site',
            'appcookie' => 'milex.connectwise.form.cookie',
        ];
    }

    /**
     * Get the array key for application cookie.
     *
     * @return string
     */
    public function getCompanyCookieKey()
    {
        return 'appcookie';
    }

    /**
     * Get the array key for companyid.
     *
     * @return string
     */
    public function getCompanyIdKey()
    {
        return 'companyid';
    }

    /**
     * Get the array key for client id.
     *
     * @return string
     */
    public function getIntegrator()
    {
        return 'username';
    }

    /**
     * Get the array key for client id.
     *
     * @return string
     */
    public function getConnectwiseUrl()
    {
        return 'site';
    }

    /**
     * Get the array key for client secret.
     *
     * @return string
     */
    public function getClientSecretKey()
    {
        return 'password';
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getSecretKeys()
    {
        return [
            'password',
        ];
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return sprintf('%s/v4_6_release/apis/3.0', $this->keys['site']);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthLoginUrl()
    {
        return $this->router->generate('milex_integration_auth_callback', ['integration' => $this->getName()]);
    }

    /**
     * @return bool
     */
    public function authCallback($settings = [], $parameters = [])
    {
        $url   = $this->getApiUrl();
        $error = false;
        try {
            $response = $this->makeRequest($url.'/system/members/', $parameters, 'GET', $settings);

            foreach ($response as $key => $r) {
                $key = preg_replace('/[\r\n]+/', '', $key);
                switch ($key) {
                    case '<!DOCTYPE_html_PUBLIC_"-//W3C//DTD_XHTML_1_0_Strict//EN"_"http://www_w3_org/TR/xhtml1/DTD/xhtml1-strict_dtd"><html_xmlns':
                        $error = '404 not found error';
                        break;
                    case 'code':
                        $error = $response['message'].' '.$r;
                        break;
                }
            }
            if (!$error) {
                $data = ['username' => $this->keys['username'], 'password' => $this->keys['password']];
                $this->extractAuthKeys($data, 'username');
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $error;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        return 'basic';
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
     */
    public function getFormLeadFields($settings = [])
    {
        return $this->getFormFieldsByObject('Contact', $settings);
    }

    /**
     * @return array|mixed
     */
    public function getAvailableLeadFields($settings = [])
    {
        $cwFields = [];
        if (isset($settings['feature_settings']['objects'])) {
            $cwObjects = $settings['feature_settings']['objects'];
        } else {
            $cwObjects['Contact'] = 'Contact';
        }
        if (!$this->isAuthorized()) {
            return [];
        }
        switch ($cwObjects) {
            case isset($cwObjects['Contact']):
                $contactFields = $this->getContactFields();

                $cwFields['Contact'] = $this->setFields($contactFields);
                break;
            case isset($cwObjects['company']):
                $company             = $this->getCompanyFields();
                $cwFields['company'] = $this->setFields($company);
                break;
        }

        return $cwFields;
    }

    /**
     * @param $fields
     *
     * @return array
     */
    public function setFields($fields)
    {
        $cwFields = [];

        foreach ($fields as $fieldName => $field) {
            if (in_array($field['type'], ['string', 'boolean', 'ref'])) {
                $cwFields[$fieldName] = [
                    'type'     => $field['type'],
                    'label'    => ucfirst($fieldName),
                    'required' => $field['required'],
                ];
            }
        }

        return $cwFields;
    }

    /**
     * @param \Milex\PluginBundle\Integration\Form|FormBuilder $builder
     * @param array                                             $data
     * @param string                                            $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ('features' == $formArea) {
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
                'objects',
                ChoiceType::class,
                [
                    'choices' => [
                        'milex.connectwise.object.contact' => 'Contact',
                        'milex.connectwise.object.company' => 'company',
                    ],
                    'expanded'          => true,
                    'multiple'          => true,
                    'label'             => 'milex.connectwise.form.objects_to_pull_from',
                    'label_attr'        => ['class' => ''],
                    'placeholder'       => false,
                    'required'          => false,
                ]
            );
        }

        if ('integration' == $formArea) {
            if ($this->isAuthorized()) {
                $builder->add(
                    'push_activities',
                    YesNoButtonGroupType::class,
                    [
                        'label'      => 'milex.plugin.config.push.activities',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => [
                            'class' => 'form-control',
                        ],
                        'data'     => (!isset($data['push_activities'])) ? true : $data['push_activities'],
                        'required' => false,
                    ]
                );

                $builder->add(
                        'campaign_task',
                        IntegrationCampaignsTaskType::class,
                        [
                            'label' => false,
                            'attr'  => [
                                'data-hide-on' => '{"campaignevent_properties_config_push_activities_0":"checked"}',
                            ],
                            'data' => (isset($data['campaign_task'])) ? $data['campaign_task'] : [],
                        ]);
            }
        }
    }

    /**
     * @return array of company fields for connectwise
     */
    public function getCompanyFields()
    {
        return [
            'identifier'            => ['type' => 'string', 'required' => true],
            'name'                  => ['type' => 'string', 'required' => true],
            'addressLine1'          => ['type' => 'string', 'required' => false],
            'addressLine2'          => ['type' => 'string', 'required' => false],
            'city'                  => ['type' => 'string', 'required' => false],
            'state'                 => ['type' => 'string', 'required' => false],
            'zip'                   => ['type' => 'string', 'required' => false],
            'phoneNumber'           => ['type' => 'string', 'required' => false],
            'faxNumber'             => ['type' => 'string', 'required' => false],
            'website'               => ['type' => 'string', 'required' => false],
            'territoryId'           => ['type' => 'string', 'required' => false],
            'marketId'              => ['type' => 'string', 'required' => false],
            'accountNumber'         => ['type' => 'string', 'required' => false],
            'dateAcquired'          => ['type' => 'string', 'required' => false],
            'annualRevenue'         => ['type' => 'string', 'required' => false],
            'numberOfEmployees'     => ['type' => 'string', 'required' => false],
            'leadSource'            => ['type' => 'string', 'required' => false],
            'leadFlag'              => ['type' => 'boolean', 'required' => false],
            'unsubscribeFlag'       => ['type' => 'boolean', 'required' => false],
            'calendarId'            => ['type' => 'string', 'required' => false],
            'userDefinedField1'     => ['type' => 'string', 'required' => false],
            'userDefinedField2'     => ['type' => 'string', 'required' => false],
            'userDefinedField3'     => ['type' => 'string', 'required' => false],
            'userDefinedField4'     => ['type' => 'string', 'required' => false],
            'userDefinedField5'     => ['type' => 'string', 'required' => false],
            'userDefinedField6'     => ['type' => 'string', 'required' => false],
            'userDefinedField7'     => ['type' => 'string', 'required' => false],
            'userDefinedField8'     => ['type' => 'string', 'required' => false],
            'userDefinedField9'     => ['type' => 'string', 'required' => false],
            'userDefinedField10'    => ['type' => 'string', 'required' => false],
            'vendorIdentifier'      => ['type' => 'string', 'required' => false],
            'taxIdentifier'         => ['type' => 'string', 'required' => false],
            'invoiceToEmailAddress' => ['type' => 'string', 'required' => false],
            'invoiceCCEmailAddress' => ['type' => 'string', 'required' => false],
            'deletedFlag'           => ['type' => 'boolean', 'required' => false],
            'dateDeleted'           => ['type' => 'string', 'required' => false],
            'deletedBy'             => ['type' => 'string', 'required' => false],
            //todo 'customFields' => 'array',
        ];
    }

    /**
     * @return array of contact fields for connectwise
     */
    public function getContactFields()
    {
        return [
            'firstName'              => ['type' => 'string', 'required' => true],
            'lastName'               => ['type' => 'string', 'required' => false],
            'type'                   => ['type' => 'string', 'required' => false],
            'company'                => ['type' => 'ref', 'required' => false, 'value' => 'name'],
            'addressLine1'           => ['type' => 'string', 'required' => false],
            'addressLine2'           => ['type' => 'string', 'required' => false],
            'city'                   => ['type' => 'string', 'required' => false],
            'state'                  => ['type' => 'string', 'required' => false],
            'zip'                    => ['type' => 'string', 'required' => false],
            'country'                => ['type' => 'string', 'required' => false],
            'inactiveFlag'           => ['type' => 'string', 'required' => false],
            'securityIdentifier'     => ['type' => 'string', 'required' => false],
            'managerContactId'       => ['type' => 'string', 'required' => false],
            'assistantContactId'     => ['type' => 'string', 'required' => false],
            'title'                  => ['type' => 'string', 'required' => false],
            'school'                 => ['type' => 'string', 'required' => false],
            'nickName'               => ['type' => 'string', 'required' => false],
            'marriedFlag'            => ['type' => 'boolean', 'required' => false],
            'childrenFlag'           => ['type' => 'boolean', 'required' => false],
            'significantOther'       => ['type' => 'string', 'required' => false],
            'portalPassword'         => ['type' => 'string', 'required' => false],
            'portalSecurityLevel'    => ['type' => 'string', 'required' => false],
            'disablePortalLoginFlag' => ['type' => 'boolean', 'required' => false],
            'unsubscribeFlag'        => ['type' => 'boolean', 'required' => false],
            'userDefinedField1'      => ['type' => 'string', 'required' => false],
            'userDefinedField2'      => ['type' => 'string', 'required' => false],
            'userDefinedField3'      => ['type' => 'string', 'required' => false],
            'userDefinedField4'      => ['type' => 'string', 'required' => false],
            'userDefinedField5'      => ['type' => 'string', 'required' => false],
            'userDefinedField6'      => ['type' => 'string', 'required' => false],
            'userDefinedField7'      => ['type' => 'string', 'required' => false],
            'userDefinedField8'      => ['type' => 'string', 'required' => false],
            'userDefinedField9'      => ['type' => 'string', 'required' => false],
            'userDefinedField10'     => ['type' => 'string', 'required' => false],
            'gender'                 => ['type' => 'string', 'required' => false],
            'birthDay'               => ['type' => 'string', 'required' => false],
            'anniversary'            => ['type' => 'string', 'required' => false],
            'presence'               => ['type' => 'string', 'required' => false],
            'mobileGuid'             => ['type' => 'string', 'required' => false],
            'facebookUrl'            => ['type' => 'string', 'required' => false],
            'twitterUrl'             => ['type' => 'string', 'required' => false],
            'linkedInUrl'            => ['type' => 'string', 'required' => false],
            'defaultBillingFlag'     => ['type' => 'boolean', 'required' => false],
            'communicationItems'     => [
                'type'     => 'array',
                'required' => false,
                'items'    => [
                    'name'  => ['type' => 'name'],
                    'value' => 'value',
                    'keys'  => ['Email', 'Direct', 'Fax', 'Cell'],
                ],
            ],
            'Direct'                 => ['type' => 'string', 'required' => false, 'configOnly' => true],
            'Cell'                   => ['type' => 'string', 'required' => false, 'configOnly' => true],
            'Email'                  => ['type' => 'string', 'required' => true, 'configOnly' => true],
            'Fax'                    => ['type' => 'string', 'required' => false, 'configOnly' => true],
        ];
    }

    /**
     * Get Contacts from connectwise.
     *
     * @param array $params
     * @param null  $query
     */
    public function getLeads($params = [], $query = null, &$executed = null, $result = [], $object = 'Contact')
    {
        return $this->getRecords($params, $object);
    }

    /**
     * Get Companies from connectwise.
     */
    public function getCompanies(array $params = [])
    {
        return $this->getRecords($params, 'company');
    }

    /**
     * @param $params
     * @param $object
     */
    public function getRecords($params, $object)
    {
        if (!$this->isAuthorized()) {
            return 0;
        }

        $page                = 1;
        $executed            = 0;
        $integrationEntities = [];
        try {
            while ($records = ('Contact' == $object)
                ? $this->getApiHelper()->getContacts($params, $page)
                : $this->getApiHelper()->getCompanies($params, $page)) {
                $milexReferenceObject = ('Contact' == $object) ? 'lead' : 'company';
                foreach ($records as $record) {
                    if (is_array($record)) {
                        $id            = $record['id'];
                        $formattedData = $this->amendLeadDataBeforeMilexPopulate($record, $object);
                        $entity        = ('Contact' == $object)
                            ? $this->getMilexLead($formattedData)
                            : $this->getMilexCompany(
                                $formattedData,
                                'company'
                            );
                        if ($entity) {
                            $integrationEntities[] = $this->saveSyncedData($entity, $object, $milexReferenceObject, $id);
                            $this->em->detach($entity);
                            unset($entity);
                            ++$executed;
                        }
                    }
                }

                if ($integrationEntities) {
                    $this->em->getRepository('MilexPluginBundle:IntegrationEntity')->saveEntities($integrationEntities);
                    $this->em->clear('Milex\PluginBundle\Entity\IntegrationEntity');
                }

                // No use checking the next page if there are less records than the requested page size

                if (count($records) < self::PAGESIZE) {
                    break;
                }

                ++$page;
            }

            return $executed;
        } catch (\Exception $e) {
            if (404 !== $e->getCode()) {
                $this->logIntegrationError($e);
            }
        }

        return $executed;
    }

    /**
     * Ammend mapped lead data before creating to Milex.
     *
     * @param $data
     * @param $object
     */
    public function amendLeadDataBeforeMilexPopulate($data, $object)
    {
        $fieldsValues = [];

        if (empty($data)) {
            return $fieldsValues;
        }
        if ('Contact' == $object) {
            $fields = $this->getContactFields();
        } else {
            $fields = $this->getCompanyFields();
        }

        foreach ($data as $key => $field) {
            if (isset($fields[$key])) {
                $name = $key;
                if ('array' == $fields[$key]['type']) {
                    $items = $fields[$key]['items'];
                    foreach ($field as $item) {
                        if (is_array($item[key($items['name'])])) {
                            foreach ($item[key($items['name'])] as $nameKey => $nameField) {
                                if ($nameKey == $items['name'][key($items['name'])]) {
                                    $name = $nameField;
                                }
                            }
                        }
                        $fieldsValues[$name] = $item[$items['value']];
                    }
                } elseif ('ref' == $fields[$key]['type']) {
                    $fieldsValues[$name] = $field[$fields[$key]['value']];
                } else {
                    $fieldsValues[$name] = $field;
                }
            }
        }
        if (isset($data['id'])) {
            $fieldsValues['id'] = $data['id'];
        }

        return $fieldsValues;
    }

    /**
     * @param $entity
     * @param $object
     * @param $milexObjectReference
     * @param $integrationEntityId
     *
     * @return IntegrationEntity|object|null
     */
    public function saveSyncedData($entity, $object, $milexObjectReference, $integrationEntityId)
    {
        /** @var IntegrationEntityRepository $integrationEntityRepo */
        $integrationEntityRepo = $this->em->getRepository('MilexPluginBundle:IntegrationEntity');
        $integrationEntities   = $integrationEntityRepo->getIntegrationEntities(
            $this->getName(),
            $object,
            $milexObjectReference,
            [$entity->getId()]
        );

        if ($integrationEntities) {
            $integrationEntity = reset($integrationEntities);
            $integrationEntity->setLastSyncDate(new \DateTime());
        } else {
            $integrationEntity = new IntegrationEntity();
            $integrationEntity->setDateAdded(new \DateTime());
            $integrationEntity->setIntegration($this->getName());
            $integrationEntity->setIntegrationEntity($object);
            $integrationEntity->setIntegrationEntityId($integrationEntityId);
            $integrationEntity->setInternalEntity($milexObjectReference);
            $integrationEntity->setInternalEntityId($entity->getId());
        }

        return $integrationEntity;
    }

    /**
     * @param array|Lead $lead
     * @param array      $config
     *
     * @return array|bool
     *
     * @throws ApiErrorException
     */
    public function pushLead($lead, $config = [])
    {
        $config      = $this->mergeConfigToFeatureSettings($config);
        $personFound = false;
        $leadPushed  = false;
        $object      = 'Contact';

        if (empty($config['leadFields']) || !$lead->getEmail()) {
            return $leadPushed;
        }

        //findLead first
        $cwContactExists = $this->getApiHelper()->getContacts(['Email' => $lead->getEmail()]);

        if (!empty($cwContactExists)) {
            $personFound = true;
        }

        $personData = [];

        try {
            if ($personFound) {
                foreach ($cwContactExists as $cwContact) { // go through array of contacts found since Connectwise lets you duplicate records with same email address
                    $mappedData = $this->getMappedFields($object, $lead, $personFound, $config, $cwContact);

                    if (!empty($mappedData)) {
                        $personData = $this->getApiHelper()->updateContact($mappedData, $cwContact['id']);
                    } else {
                        $personData['id'] = $cwContact['id'];
                    }
                }
            } else {
                $mappedData = $this->getMappedFields($object, $lead, $personFound, $config);
                $personData = $this->getApiHelper()->createContact($mappedData);
            }

            if (!empty($personData['id'])) {
                $id                    = $personData['id'];
                $integrationEntities[] = $this->saveSyncedData($lead, $object, 'lead', $id);

                if (isset($config['push_activities']) and true == $config['push_activities']) {
                    $savedEntities = $this->createActivity($config['campaign_task'], $id, $lead->getId());
                    if ($savedEntities) {
                        $integrationEntities[] = $savedEntities;
                    }
                }

                if (!empty($integrationEntities)) {
                    $this->em->getRepository('MilexPluginBundle:IntegrationEntity')->saveEntities($integrationEntities);
                    $this->em->clear('Milex\PluginBundle\Entity\IntegrationEntity');
                }

                $leadPushed = true;
            }
        } catch (\Exception $e) {
            if ($e instanceof ApiErrorException) {
                $e->setContact($lead);
            }
            $this->logIntegrationError($e);
        }

        return $leadPushed;
    }

    /**
     * @param $cwContactData
     * @param $object
     * @param $lead
     * @param $personFound
     * @param $config
     *
     * @return array
     */
    public function getMappedFields($object, $lead, $personFound, $config, $cwContactData = [])
    {
        $fieldsToUpdateInCW = isset($config['update_milex']) && $personFound ? array_keys($config['update_milex'], 1) : [];
        $objectFields       = $this->prepareFieldsForPush($this->getContactFields());
        $leadFields         = $config['leadFields'];

        $cwContactExists = $this->amendLeadDataBeforeMilexPopulate($cwContactData, $object);

        $communicationItems = isset($cwContactData['communicationItems']) ? $cwContactData['communicationItems'] : [];

        $leadFields = array_diff_key($leadFields, array_flip($fieldsToUpdateInCW));
        $leadFields = $this->getBlankFieldsToUpdate($leadFields, $cwContactExists, $objectFields, $config);

        return $this->populateLeadData(
            $lead,
            [
                'leadFields'       => $leadFields,
                'object'           => 'Contact',
                'feature_settings' => [
                    'objects' => $config['objects'],
                ],
                'update'             => $personFound,
                'communicationItems' => $communicationItems,
            ]
        );
    }

    /**
     * Match lead data with integration fields.
     *
     * @param $lead
     * @param $config
     *
     * @return array
     */
    public function populateLeadData($lead, $config = [])
    {
        if ($lead instanceof Lead) {
            $fields = $lead->getFields(true);
        } else {
            $fields = $lead;
        }

        $leadFields = $config['leadFields'];
        if (empty($leadFields)) {
            return [];
        }
        $availableFields = $this->getContactFields();
        $unknown         = $this->translator->trans('milex.integration.form.lead.unknown');
        $matched         = [];

        foreach ($availableFields as $key => $field) {
            $integrationKey = $matchIntegrationKey = $key;

            if (isset($field['configOnly'])) {
                continue;
            }

            if ('communicationItems' == $integrationKey) {
                $communicationItems = [];
                foreach ($field['items']['keys'] as $keyItem => $item) {
                    $defaultValue = [];
                    $keyExists    = false;
                    if (isset($leadFields[$item])) {
                        if ('Email' == $item) {
                            $defaultValue = ['defaultFlag' => true];
                        }
                        $milexKey = $leadFields[$item];
                        if (isset($fields[$milexKey]) && !empty($fields[$milexKey]['value'])) {
                            foreach ($config['communicationItems'] as $key => $ci) {
                                if ($ci['type']['id'] == $keyItem + 1) {
                                    $config['communicationItems'][$key]['value'] = $fields[$milexKey]['value'];
                                    $keyExists                                   = true;
                                }
                            }
                            if (!$keyExists) {
                                $type = [
                                    'type' => ['id' => $keyItem + 1, 'name' => $item], ];
                                $values = array_merge(['value' => $this->cleanPushData($fields[$milexKey]['value'])], $defaultValue);

                                $communicationItems[] = array_merge($type, $values);
                            }
                        }
                    }
                }

                if ($config['update']) {
                    $communicationItems = array_merge($config['communicationItems'], $communicationItems);
                }
                if (!empty($communicationItems)) {
                    $matched[$integrationKey] = $communicationItems;
                }
            }

            if ('company' === $integrationKey && !empty($fields['company']['value'])) {
                try {
                    $foundCompanies = $this->getApiHelper()->getCompanies([
                        'conditions' => [
                            sprintf('Name = "%s"', $fields['company']['value']),
                        ],
                    ]);

                    $matched['company'] = ['identifier' => $foundCompanies[0]['identifier']];
                } catch (ApiErrorException $e) {
                    // No matching companies were found
                }

                continue;
            }

            if (isset($leadFields[$integrationKey])) {
                $milexKey = $leadFields[$integrationKey];
                if (isset($fields[$milexKey]) && !empty($fields[$milexKey]['value'])) {
                    $matched[$matchIntegrationKey] = $this->cleanPushData($fields[$milexKey]['value']);
                }
            }

            if (!empty($field['required']) && empty($matched[$matchIntegrationKey]) && !$config['update']) {
                $matched[$matchIntegrationKey] = $unknown;
            }
        }

        if ($config['update']) {
            $updateFields = [];
            foreach ($matched as $key => $field) {
                $updateFields[] = [
                    'op'    => 'replace',
                    'path'  => $key,
                    'value' => $field,
                ];
            }
            $matched = $updateFields;
        }

        return $matched;
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
            $objects = ['Leads', 'Contacts'];
        }
        if (isset($fieldsToUpdate['leadFields']) && is_array($objects)) {
            // Pass in the whole config
            $fields = $fieldsToUpdate['leadFields'];
        } else {
            $fields = array_flip($fieldsToUpdate);
        }

        return $this->prepareFieldsForSync($fields, $fieldsToUpdate, $objects);
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
        if ('company' == $object) {
            $priority = parent::getPriorityFieldsForMilex($config, $object, 'milex_company');
            $fields   = array_intersect_key($config['companyFields'], $priority);
        } else {
            $fields = parent::getPriorityFieldsForMilex($config, $object, $priorityObject);
        }

        return ($object && isset($fields[$object])) ? $fields[$object] : $fields;
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

        if (!empty($campaigns)) {
            foreach ($campaigns as $campaign) {
                if (isset($campaign['id'])) {
                    $choices[] = [
                        'value' => $campaign['id'],
                        'label' => $campaign['name'],
                    ];
                }
            }
        }

        return $choices;
    }

    /**
     * @param $campaignId
     *
     * @return bool
     */
    public function getCampaignMembers($campaignId)
    {
        if (!$this->isAuthorized()) {
            return false;
        }

        try {
            $page = 1;
            while ($campaignsMembersResults = $this->getApiHelper()->getCampaignMembers($campaignId, $page)) {
                $campaignMemberObject = new IntegrationObject('CampaignMember', 'lead');
                $recordList           = $this->getRecordList($campaignsMembersResults, 'id');
                $contacts             = (array) $this->integrationEntityModel->getSyncedRecords(new IntegrationObject('Contact', 'lead'), $this->getName(), $recordList);

                $existingContactsIds = array_column(array_filter(
                    $contacts,
                    function ($contact) {
                        return 'lead' === $contact['internal_entity'];
                    }
                ), 'integration_entity_id');

                $contactsToFetch = array_diff_key($recordList, array_flip($existingContactsIds));

                if (!empty($contactsToFetch)) {
                    $listOfContactsToFetch = implode(',', array_keys($contactsToFetch));
                    $params['Ids']         = $listOfContactsToFetch;

                    $this->getLeads($params);
                }

                $saveCampaignMembers = array_merge($existingContactsIds, array_keys($contactsToFetch));

                $this->saveCampaignMembers($saveCampaignMembers, $campaignMemberObject, $campaignId);

                if (count($campaignsMembersResults) < self::PAGESIZE) {
                    // No use continuing as we have less results than page size
                    break;
                }

                ++$page;
            }

            return true;
        } catch (\Exception $e) {
            if (404 !== $e->getCode()) {
                $this->logIntegrationError($e);
            }
        }

        return false;
    }

    /**
     * @param $allCampaignMembers
     * @param $campaignMemberObject
     * @param $campaignId
     */
    public function saveCampaignMembers($allCampaignMembers, $campaignMemberObject, $campaignId)
    {
        if (empty($allCampaignMembers)) {
            return;
        }
        $persistEntities = [];
        $recordList      = $this->getRecordList($allCampaignMembers);
        $milexObject    = new IntegrationObject('Contact', 'lead');

        $contacts = $this->integrationEntityModel->getSyncedRecords($milexObject, $this->getName(), $recordList);
        //first find existing campaign members.
        foreach ($contacts as $contact) {
            $existingCampaignMember = $this->integrationEntityModel->getSyncedRecords($campaignMemberObject, $this->getName(), $campaignId, $contact['internal_entity_id']);
            if (empty($existingCampaignMember)) {
                $persistEntities[] = $this->createIntegrationEntity(
                    $campaignMemberObject->getType(),
                    $campaignId,
                    $campaignMemberObject->getInternalType(),
                    $contact['internal_entity_id'],
                    [],
                    false
                );
            }
        }

        if ($persistEntities) {
            $this->em->getRepository('MilexPluginBundle:IntegrationEntity')->saveEntities($persistEntities);
            unset($persistEntities);
            $this->em->clear(IntegrationEntity::class);
        }
    }

    /**
     * @param $records
     *
     * @return array
     */
    public function getRecordList($records, $index = null)
    {
        $recordList = [];

        foreach ($records as $record) {
            if ($index && isset($record[$index])) {
                $record = $record[$index];
            }

            $recordList[$record] = [
                'id' => $record,
            ];
        }

        return $recordList;
    }

    /**
     * @return array
     *
     * @throws ApiErrorException
     */
    public function getActivityTypes()
    {
        $activities   = [];
        $cwActivities = $this->getApiHelper()->getActivityTypes();

        foreach ($cwActivities as $cwActivity) {
            if (isset($cwActivity['id'])) {
                $activities[$cwActivity['id']] = $cwActivity['name'];
            }
        }

        return $activities;
    }

    /**
     * @return array
     *
     * @throws ApiErrorException
     */
    public function getMembers()
    {
        $members   = [];
        $cwMembers = $this->getApiHelper()->getMembers();
        foreach ($cwMembers as $cwMember) {
            if (isset($cwMember['id'])) {
                $members[$cwMember['id']] = $cwMember['identifier'];
            }
        }

        return $members;
    }

    /**
     * @param $config
     * @param $cwContactId
     * @param $leadId
     *
     * @return IntegrationEntity|null
     *
     * @throws ApiErrorException
     */
    public function createActivity($config, $cwContactId, $leadId)
    {
        if ($cwContactId and !empty($config['activity_name'])) {
            $activity = [
                'name'     => $config['activity_name'],
                'type'     => ['id' => $config['campaign_activity_type']],
                'assignTo' => ['id' => $config['campaign_members']],
                'contact'  => ['id' => $cwContactId],
            ];
            $activities = $this->getApiHelper()->postActivity($activity);

            if (isset($activities['id'])) {
                return $this->createIntegrationEntity('Activities', $activities['id'], 'lead', $leadId, null, false);
            }
        }

        return null;
    }
}