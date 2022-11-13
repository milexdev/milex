<?php

namespace Milex\LeadBundle\Deduplicate;

use Milex\LeadBundle\Entity\CompanyRepository;
use Milex\LeadBundle\Exception\UniqueFieldNotFoundException;
use Milex\LeadBundle\Model\FieldModel;

class CompanyDeduper
{
    use DeduperTrait;

    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    /**
     * DedupModel constructor.
     */
    public function __construct(FieldModel $fieldModel, CompanyRepository $companyRepository)
    {
        $this->fieldModel        = $fieldModel;
        $this->companyRepository = $companyRepository;
        $this->object            = 'company';
    }

    /**
     * @return Company[]
     *
     * @throws UniqueFieldNotFoundException
     */
    public function checkForDuplicateCompanies(array $queryFields): array
    {
        $uniqueData = $this->getUniqueData($queryFields);
        if (empty($uniqueData)) {
            throw new UniqueFieldNotFoundException();
        }

        return $this->companyRepository->getCompaniesByUniqueFields($uniqueData);
    }
}
