<?php

namespace MilexPlugin\MilexCrmBundle\Integration\Pipedrive\Import;

use Milex\UserBundle\Entity\User;
use MilexPlugin\MilexCrmBundle\Entity\PipedriveOwner;
use MilexPlugin\MilexCrmBundle\Integration\Pipedrive\AbstractPipedrive;

abstract class AbstractImport extends AbstractPipedrive
{
    /**
     * @param $params
     * @param $endpoint
     *
     * @return array
     */
    public function getData($params, $endpoint)
    {
        $result = [
            'processed'                => 0,
            'more_items_in_collection' => false,
        ];

        try {
            if ($this->getIntegration()->isAuthorized()) {
                $data = $this->getIntegration()->getApiHelper()->getDataByEndpoint($params, $endpoint);

                if (!empty($data['data'])) {
                    foreach ($data['data'] as $object) {
                        try {
                            $this->create($object);
                            ++$result['processed'];
                        } catch (\Exception $e) {
                            $this->getIntegration()->logIntegrationError($e);
                        }
                    }
                }

                if (isset($data['additional_data']['pagination'])) {
                    $result['more_items_in_collection'] = $data['additional_data']['pagination']['more_items_in_collection'];
                } else {
                    $result['more_items_in_collection'] = false;
                }
            }
        } catch (\Exception $e) {
            $this->getIntegration()->logIntegrationError($e);
        }

        return $result;
    }

    /**
     * @return bool
     */
    abstract protected function create(array $data = []);

    /**
     * @param $id
     */
    protected function getOwnerByIntegrationId($id)
    {
        $pipedriveOwner = $this->em->getRepository(PipedriveOwner::class)->findOneByOwnerId($id);

        if (!$pipedriveOwner) {
            return null;
        }

        $milexOwner = $this->em->getRepository(User::class)->findOneByEmail($pipedriveOwner->getEmail());

        if (!$milexOwner) {
            return null;
        }

        return $milexOwner;
    }
}
