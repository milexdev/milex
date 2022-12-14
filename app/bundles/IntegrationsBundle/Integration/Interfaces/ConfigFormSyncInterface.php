<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Integration\Interfaces;

use Milex\IntegrationsBundle\Mapping\MappedFieldInfoInterface;

interface ConfigFormSyncInterface extends IntegrationInterface
{
    /**
     * Return an array of Integration objects in the format of [$object => $translatableObjectNameString].
     * i.e. ['Customer' => 'milex.something.object.customer', 'Account' => 'milex.something.object.account'];.
     */
    public function getSyncConfigObjects(): array;

    /**
     * Return an array of Integration objects and what Milex objects they are mapped to.
     * i.e. ['Customer' => Contact::NAME, 'Account' =>  Company::NAME];.
     */
    public function getSyncMappedObjects(): array;

    /**
     * Return an array of required fields.
     *
     * @return MappedFieldInfoInterface[]
     */
    public function getRequiredFieldsForMapping(string $object): array;

    /**
     * Return an array of optional fields.
     *
     * @return MappedFieldInfoInterface[]
     */
    public function getOptionalFieldsForMapping(string $object): array;

    /**
     * Return an array of all fields.
     *
     * @return MappedFieldInfoInterface[]
     */
    public function getAllFieldsForMapping(string $object): array;

    /**
     * Return a custom form field name to be included in the features array specific to sync.
     */
    public function getSyncConfigFormName(): ?string;
}
