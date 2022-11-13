<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder;

use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO as ReportFieldDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO as RequestObjectDAO;
use Milex\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use Milex\IntegrationsBundle\Sync\Exception\FieldNotFoundException;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Helper\FieldHelper;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectHelper\ContactObjectHelper;
use Milex\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class FieldBuilder
{
    /**
     * @var ValueNormalizer
     */
    private $valueNormalizer;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var ContactObjectHelper
     */
    private $contactObjectHelper;

    /**
     * @var array
     */
    private $milexObject;

    /**
     * @var RequestObjectDAO
     */
    private $requestObject;

    public function __construct(Router $router, FieldHelper $fieldHelper, ContactObjectHelper $contactObjectHelper)
    {
        $this->valueNormalizer = new ValueNormalizer();

        $this->router              = $router;
        $this->fieldHelper         = $fieldHelper;
        $this->contactObjectHelper = $contactObjectHelper;
    }

    /**
     * @return ReportFieldDAO
     *
     * @throws FieldNotFoundException
     */
    public function buildObjectField(
        string $field,
        array $milexObject,
        RequestObjectDAO $requestObject,
        string $integration,
        string $defaultState = ReportFieldDAO::FIELD_CHANGED
    ) {
        $this->milexObject  = $milexObject;
        $this->requestObject = $requestObject;

        // Special handling of the ID field
        if ('milex_internal_id' === $field) {
            return $this->addContactIdField($field);
        }

        // Special handling of the owner ID field
        if ('owner_id' === $field) {
            return $this->createOwnerIdReportFieldDAO($field, (int) $milexObject['owner_id']);
        }

        // Special handling of DNC fields
        if (0 === strpos($field, 'milex_internal_dnc_')) {
            return $this->addDoNotContactField($field);
        }

        // Special handling of timeline URL
        if ('milex_internal_contact_timeline' === $field) {
            return $this->addContactTimelineField($integration, $field);
        }

        return $this->addCustomField($field, $defaultState);
    }

    /**
     * @return ReportFieldDAO
     */
    private function addContactIdField(string $field)
    {
        $normalizedValue = new NormalizedValueDAO(
            NormalizedValueDAO::INT_TYPE,
            $this->milexObject['id']
        );

        return new ReportFieldDAO($field, $normalizedValue);
    }

    /**
     * @return ReportFieldDAO
     */
    private function createOwnerIdReportFieldDAO(string $field, int $ownerId)
    {
        return new ReportFieldDAO(
            $field,
            new NormalizedValueDAO(
                NormalizedValueDAO::INT_TYPE,
                $ownerId
            )
        );
    }

    /**
     * @return ReportFieldDAO
     */
    private function addDoNotContactField(string $field)
    {
        $channel = str_replace('milex_internal_dnc_', '', $field);

        $normalizedValue = new NormalizedValueDAO(
            NormalizedValueDAO::INT_TYPE,
            $this->contactObjectHelper->getDoNotContactStatus((int) $this->milexObject['id'], $channel)
        );

        return new ReportFieldDAO($field, $normalizedValue);
    }

    /**
     * @return ReportFieldDAO
     */
    private function addContactTimelineField(string $integration, string $field)
    {
        $normalizedValue = new NormalizedValueDAO(
            NormalizedValueDAO::URL_TYPE,
            $this->router->generate(
                'milex_plugin_timeline_view',
                [
                    'integration' => $integration,
                    'leadId'      => $this->milexObject['id'],
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );

        return new ReportFieldDAO($field, $normalizedValue);
    }

    /**
     * @return ReportFieldDAO
     *
     * @throws FieldNotFoundException
     */
    private function addCustomField(string $field, string $defaultState)
    {
        // The rest should be Milex custom fields and if not, just ignore
        $milexFields = $this->fieldHelper->getFieldList($this->requestObject->getObject());
        if (!isset($milexFields[$field])) {
            // Field must have been deleted or something so let's skip
            throw new FieldNotFoundException($field, $this->requestObject->getObject());
        }

        $requiredFields  = $this->requestObject->getRequiredFields();
        $fieldType       = $this->fieldHelper->getNormalizedFieldType($milexFields[$field]['type']);
        $normalizedValue = $this->valueNormalizer->normalizeForMilex($fieldType, $this->milexObject[$field]);

        return new ReportFieldDAO(
            $field,
            $normalizedValue,
            in_array($field, $requiredFields) ? ReportFieldDAO::FIELD_REQUIRED : $defaultState
        );
    }
}
