<?php

/*
 * @copyright   2018 Milex Inc. All rights reserved
 * @author      Milex, Inc.
 *
 * @link        https://www.milex.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Tests\Functional\Services\SyncService\TestExamples\Integration;

use Milex\IntegrationsBundle\Integration\BasicIntegration;
use Milex\IntegrationsBundle\Integration\Interfaces\IntegrationInterface;
use Milex\IntegrationsBundle\Integration\Interfaces\SyncInterface;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use Milex\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\SyncDataExchangeInterface;
use Milex\IntegrationsBundle\Tests\Functional\Services\SyncService\TestExamples\Sync\SyncDataExchange\ExampleSyncDataExchange;

final class ExampleIntegration extends BasicIntegration implements IntegrationInterface, SyncInterface
{
    const NAME = 'Example';

    /**
     * @var ExampleSyncDataExchange
     */
    private $syncDataExchange;

    /**
     * ExampleIntegration constructor.
     */
    public function __construct(ExampleSyncDataExchange $syncDataExchange)
    {
        $this->syncDataExchange = $syncDataExchange;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function isAuthorized(): bool
    {
        return true;
    }

    /**
     * Get if data priority is enabled in the integration or not default is false.
     */
    public function getDataPriority(): bool
    {
        return true;
    }

    public function getSyncDataExchange(): SyncDataExchangeInterface
    {
        return $this->syncDataExchange;
    }

    public function getMappingManual(): MappingManualDAO
    {
        // Generate mapping manual that will be passed to the sync service. This instructs the sync service how to map Milex fields to integration fields
        $mappingManual = new MappingManualDAO(self::NAME);

        // Each object like lead, contact, user, company, account, etc, will need it's own ObjectMappingDAO
        // In this example, Milex's Contact object is mapped to the Example's Lead object
        $leadObjectMapping = new ObjectMappingDAO(
            Contact::NAME,
            ExampleSyncDataExchange::OBJECT_LEAD
        );
        $mappingManual->addObjectMapping($leadObjectMapping);

        // Get field mapping as configured in Milex's integration config
        $mappedFields = $this->getConfiguredFieldMapping();

        foreach ($mappedFields as $integrationField => $milexField) {
            // In this case, we're just adding each field to each of the objects
            // Of course, other integrations may need more logic

            // Sync bidirectionally by default but also can use ObjectMappingDAO::SYNC_TO_MILEX or ObjectMappingDAO::SYNC_TO_INTEGRATION

            if ('email' === $milexField) {
                // Set email as a required field so that it maps a value regardless if changed
                $leadObjectMapping->addFieldMapping($milexField, $integrationField, ObjectMappingDAO::SYNC_BIDIRECTIONALLY, true);
            } else {
                $leadObjectMapping->addFieldMapping($milexField, $integrationField);
            }
        }

        return $mappingManual;
    }

    /**
     * Likely will get this mapping out of the Integration's settings.
     *
     * @return array
     */
    private function getConfiguredFieldMapping()
    {
        return [
            'first_name' => 'firstname',
            'last_name'  => 'lastname',
            'email'      => 'email',
            'street1'    => 'address1',
        ];
    }
}
