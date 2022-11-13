<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Field;

use Milex\LeadBundle\Entity\LeadField;
use Milex\LeadBundle\Entity\LeadFieldRepository;
use Milex\LeadBundle\Exception\NoListenerException;
use Milex\LeadBundle\Field\Dispatcher\FieldSaveDispatcher;

class LeadFieldSaver
{
    /**
     * @var LeadFieldRepository
     */
    private $leadFieldRepository;

    /**
     * @var FieldSaveDispatcher
     */
    private $fieldSaveDispatcher;

    public function __construct(LeadFieldRepository $leadFieldRepository, FieldSaveDispatcher $fieldSaveDispatcher)
    {
        $this->leadFieldRepository = $leadFieldRepository;
        $this->fieldSaveDispatcher = $fieldSaveDispatcher;
    }

    public function saveLeadFieldEntity(LeadField $leadField, bool $isNew): void
    {
        try {
            $this->fieldSaveDispatcher->dispatchPreSaveEvent($leadField, $isNew);
        } catch (NoListenerException $e) {
        }

        $this->leadFieldRepository->saveEntity($leadField);

        try {
            $this->fieldSaveDispatcher->dispatchPostSaveEvent($leadField, $isNew);
        } catch (NoListenerException $e) {
        }
    }

    public function saveLeadFieldEntityWithoutColumnCreated(LeadField $leadField): void
    {
        $leadField->setColumnIsNotCreated();

        $this->saveLeadFieldEntity($leadField, true);
    }
}
