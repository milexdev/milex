<?php

namespace Milex\CoreBundle\Controller;

use Milex\CoreBundle\Factory\ModelFactory;
use Milex\CoreBundle\Helper\InputHelper;
use Milex\CoreBundle\Model\AjaxLookupModelInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

trait AjaxLookupControllerTrait
{
    /**
     * @return JsonResponse
     */
    protected function getLookupChoiceListAction(Request $request)
    {
        $dataArray = [];
        $modelName = InputHelper::clean($request->query->get('searchKey'));
        $search    = InputHelper::clean($request->query->get(str_replace('.', '_', $modelName)));
        $limit     = (int) $request->query->get('limit', 0);
        $start     = (int) $request->query->get('start', 0);
        $options   = $request->query->all();

        if (!empty($modelName) && !empty($search)) {
            /** @var ModelFactory $modelFactory */
            $modelFactory = $this->get('milex.model.factory');

            if ($modelFactory->hasModel($modelName)) {
                $model = $modelFactory->getModel($modelName);

                if ($model instanceof AjaxLookupModelInterface) {
                    $results = $model->getLookupResults($modelName, $search, $limit, $start, $options);

                    foreach ($results as $group => $result) {
                        $option = [];
                        if (is_array($result)) {
                            if (!isset($result['value'])) {
                                // Grouped options
                                $option = [
                                    'group' => true,
                                    'text'  => $group,
                                    'items' => $result,
                                ];

                                foreach ($result as $value => $label) {
                                    if (is_array($label) && isset($label['label'])) {
                                        $option['items'][$value]['text'] = $label['label'];
                                    }
                                }
                            } else {
                                if (isset($result['label'])) {
                                    $option['text'] = $result['label'];
                                }

                                $option['value'] = $result['value'];
                            }
                        } else {
                            $option[$group] = $result;
                        }

                        if (!empty($option)) {
                            $dataArray[] = $option;
                        }
                    }
                }
            }
        }

        return new JsonResponse($dataArray);
    }
}
