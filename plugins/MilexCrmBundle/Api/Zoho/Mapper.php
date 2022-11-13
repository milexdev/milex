<?php

namespace MilexPlugin\MilexCrmBundle\Api\Zoho;

use MilexPlugin\MilexCrmBundle\Api\Zoho\Exception\MatchingKeyNotFoundException;

class Mapper
{
    /**
     * @var array
     */
    private $contact = [];

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var array
     */
    private $mappedFields = [];

    private $object;

    /**
     * @var array[]
     */
    private $objectMappedValues = [];

    /**
     * Used to keep track of the key used to map contact ID with the response Zoho returns.
     *
     * @var int
     */
    private $objectCounter = 0;

    /**
     * Used to map contact ID with the response Zoho returns.
     *
     * @var array
     */
    private $contactMapper = [];

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @param $object
     *
     * @return $this
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return $this
     */
    public function setContact(array $contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return $this
     */
    public function setMappedFields(array $fields)
    {
        $this->mappedFields = $fields;

        return $this;
    }

    /**
     * @param int      $milexContactId Milex Contact ID
     * @param int|null $zohoId          Zoho ID if known
     *
     * @return int If any single field is mapped, return 1 to count as one contact to be updated
     */
    public function map($milexContactId, $zohoId = null)
    {
        $mapped             = 0;
        $objectMappedValues = [];

        foreach ($this->mappedFields as $zohoField => $milexField) {
            $field = $this->getField($zohoField);
            if ($field && isset($this->contact[$milexField]) && $this->contact[$milexField]) {
                $mapped   = 1;
                $apiField = $field['api_name'];
                $apiValue = $this->contact[$milexField];

                $objectMappedValues[$apiField] = $apiValue;
            }

            if ($zohoId) {
                $objectMappedValues['id'] = $zohoId;
            }
        }

        $this->objectMappedValues[$this->objectCounter] = $objectMappedValues;
        $this->contactMapper[$this->objectCounter]      = $milexContactId;

        ++$this->objectCounter;

        return $mapped;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return $this->objectMappedValues;
    }

    /**
     * @param int $key
     *
     * @return int
     *
     * @throws MatchingKeyNotFoundException
     */
    public function getContactIdByKey($key)
    {
        if (isset($this->contactMapper[$key])) {
            return $this->contactMapper[$key];
        }

        throw new MatchingKeyNotFoundException();
    }

    /**
     * @param $fieldName
     *
     * @return mixed
     */
    private function getField($fieldName)
    {
        return isset($this->fields[$this->object][$fieldName])
            ?
            $this->fields[$this->object][$fieldName]
            :
            null;
    }
}
