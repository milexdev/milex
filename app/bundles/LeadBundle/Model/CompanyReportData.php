<?php

namespace Milex\LeadBundle\Model;

use Milex\FormBundle\Entity\Field;
use Milex\ReportBundle\Event\ReportGeneratorEvent;
use Symfony\Component\Translation\TranslatorInterface;

class CompanyReportData
{
    /**
     * @var FieldModel
     */
    private $fieldModel;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * CompanyReportData constructor.
     */
    public function __construct(FieldModel $fieldModel, TranslatorInterface $translator)
    {
        $this->fieldModel = $fieldModel;
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function getCompanyData()
    {
        $companyColumns = $this->getCompanyColumns();
        $companyFields  = $this->fieldModel->getEntities([
            'filter' => [
                'force' => [
                    [
                        'column' => 'f.object',
                        'expr'   => 'like',
                        'value'  => 'company',
                    ],
                ],
            ],
        ]);

        return array_merge($companyColumns, $this->getFieldColumns($companyFields, 'comp.'));
    }

    /**
     * @return bool
     */
    public function eventHasCompanyColumns(ReportGeneratorEvent $event)
    {
        $companyColumns = $this->getCompanyData();
        foreach ($companyColumns as $key => $column) {
            if ($event->hasColumn($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    private function getCompanyColumns()
    {
        return [
            'comp.id' => [
                'alias' => 'comp_id',
                'label' => 'milex.lead.report.company.company_id',
                'type'  => 'int',
                'link'  => 'milex_company_action',
            ],
            'companies_lead.is_primary' => [
                'label' => 'milex.lead.report.company.is_primary',
                'type'  => 'bool',
            ],
            'companies_lead.date_added' => [
                'label' => 'milex.lead.report.company.date_added',
                'type'  => 'datetime',
            ],
        ];
    }

    /**
     * @param Field[] $fields
     * @param string  $prefix
     *
     * @return array
     */
    private function getFieldColumns($fields, $prefix)
    {
        $columns = [];
        foreach ($fields as $f) {
            switch ($f->getType()) {
                case 'boolean':
                    $type = 'bool';
                    break;
                case 'date':
                    $type = 'date';
                    break;
                case 'datetime':
                    $type = 'datetime';
                    break;
                case 'time':
                    $type = 'time';
                    break;
                case 'url':
                    $type = 'url';
                    break;
                case 'email':
                    $type = 'email';
                    break;
                case 'number':
                    $type = 'float';
                    break;
                default:
                    $type = 'string';
                    break;
            }
            $columns[$prefix.$f->getAlias()] = [
                'label' => $this->translator->trans('milex.report.field.company.label', ['%field%' => $f->getLabel()]),
                'type'  => $type,
            ];
        }

        return $columns;
    }
}
