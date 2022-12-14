<?php

namespace MilexPlugin\MilexCrmBundle\Integration\Pipedrive\Import;

use Doctrine\ORM\EntityManager;
use Milex\LeadBundle\Entity\Company;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Model\CompanyModel;
use Milex\LeadBundle\Model\LeadModel;
use Symfony\Component\HttpFoundation\Response;

class LeadImport extends AbstractImport
{
    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * @var CompanyModel
     */
    private $companyModel;

    /**
     * LeadImport constructor.
     */
    public function __construct(EntityManager $em, LeadModel $leadModel, CompanyModel $companyModel)
    {
        parent::__construct($em);

        $this->leadModel    = $leadModel;
        $this->companyModel = $companyModel;
    }

    /**
     * @return bool
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function create(array $data = [])
    {
        $integrationEntity = $this->getLeadIntegrationEntity(['integrationEntityId' => $data['id']]);

        if ($integrationEntity) {
            throw new \Exception('Lead already have integration', Response::HTTP_CONFLICT);
        }
        $data         = $this->convertPipedriveData($data, $this->getIntegration()->getApiHelper()->getFields(self::PERSON_ENTITY_TYPE));
        $dataToUpdate = $this->getIntegration()->populateMilexLeadData($data);

        if (!$lead =  $this->leadModel->checkForDuplicateContact($dataToUpdate)) {
            $lead = new Lead();
        }
        // prevent listeners from exporting
        $lead->setEventData('pipedrive.webhook', 1);

        $this->leadModel->setFieldValues($lead, $dataToUpdate);

        if (isset($data['owner_id'])) {
            $this->addOwnerToLead($data['owner_id'], $lead);
        }

        $this->leadModel->saveEntity($lead);

        $integrationEntity = $this->getLeadIntegrationEntity(['integrationEntityId' => $data['id']]);
        if (!$integrationEntity) {
            $integrationEntity = $this->createIntegrationLeadEntity(new \DateTime(), $data['id'], $lead->getId());
        }

        $this->em->persist($integrationEntity);
        $this->em->flush();

        if (isset($data['org_id']) && $this->getIntegration()->isCompanySupportEnabled()) {
            $this->addLeadToCompany($data['org_id'], $lead);
            $this->em->flush();
        }

        return true;
    }

    /**
     * @return bool
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function update(array $data = [])
    {
        $integrationEntity = $this->getLeadIntegrationEntity(['integrationEntityId' => $data['id']]);

        if (!$integrationEntity) {
            return $this->create($data);
        }

        /** @var Lead $lead * */
        $lead = $this->leadModel->getEntity($integrationEntity->getInternalEntityId());

        // prevent listeners from exporting
        $lead->setEventData('pipedrive.webhook', 1);

        $data         = $this->convertPipedriveData($data, $this->getIntegration()->getApiHelper()->getFields(self::PERSON_ENTITY_TYPE));
        $dataToUpdate = $this->getIntegration()->populateMilexLeadData($data);

        $lastSyncDate      = $integrationEntity->getLastSyncDate();
        $leadDateModified  = $lead->getDateModified();

        if ($lastSyncDate->format('Y-m-d H:i:s') >= $data['update_time']) {
            return false;
        } //Do not push lead if contact was modified in Milex, and we don't wanna mofify it

        $lead->setDateModified(new \DateTime());
        $this->leadModel->setFieldValues($lead, $dataToUpdate, true);

        if (!isset($data['owner_id']) && $lead->getOwner()) {
            $lead->setOwner(null);
        } elseif (isset($data['owner_id'])) {
            $this->addOwnerToLead($data['owner_id'], $lead);
        }
        $this->leadModel->saveEntity($lead);

        $integrationEntity->setLastSyncDate(new \DateTime());
        $this->em->persist($integrationEntity);
        $this->em->flush();

        if (!$this->getIntegration()->isCompanySupportEnabled()) {
            return;
        }

        if (empty($data['org_id']) && $lead->getCompany()) {
            $this->removeLeadFromCompany($lead->getCompany(), $lead);
        } elseif (isset($data['org_id'])) {
            $this->addLeadToCompany($data['org_id'], $lead);
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    public function delete(array $data = [])
    {
        $integrationEntity = $this->getLeadIntegrationEntity(['integrationEntityId' => $data['id']]);

        if (!$integrationEntity) {
            throw new \Exception('Lead doesn\'t have integration', Response::HTTP_NOT_FOUND);
        }

        /** @var Lead $lead */
        $lead = $this->em->getRepository(Lead::class)->findOneById($integrationEntity->getInternalEntityId());

        if (!$lead) {
            throw new \Exception('Lead doesn\'t exists in Milex', Response::HTTP_NOT_FOUND);
        }

        // prevent listeners from exporting
        $lead->setEventData('pipedrive.webhook', 1);

        $this->leadModel->deleteEntity($lead);

        if (!empty($lead->deletedId)) {
            $this->em->remove($integrationEntity);
        }
    }

    /**
     * @param $integrationOwnerId
     */
    private function addOwnerToLead($integrationOwnerId, Lead $lead)
    {
        $milexOwner = $this->getOwnerByIntegrationId($integrationOwnerId);
        $lead->setOwner($milexOwner);
    }

    /**
     * @param $companyName
     *
     * @throws \Doctrine\ORM\ORMException
     */
    private function removeLeadFromCompany($companyName, Lead $lead)
    {
        $company = $this->em->getRepository(Company::class)->findOneByName($companyName);

        if (!$company) {
            return;
        }

        $this->companyModel->removeLeadFromCompany($company, $lead);
    }

    /**
     * @param $integrationCompanyId
     *
     * @throws \Doctrine\ORM\ORMException
     */
    private function addLeadToCompany($integrationCompanyId, Lead $lead)
    {
        $integrationEntityCompany = $this->getCompanyIntegrationEntity(['integrationEntityId' => $integrationCompanyId]);

        if (!$integrationEntityCompany) {
            return;
        }

        if (!$company = $this->companyModel->getEntity($integrationEntityCompany->getInternalEntityId())) {
            return;
        }

        $this->companyModel->addLeadToCompany($company, $lead);
    }
}
