<?php

namespace Milex\StageBundle\Model;

use Milex\CoreBundle\Helper\Chart\ChartQuery;
use Milex\CoreBundle\Helper\Chart\LineChart;
use Milex\CoreBundle\Helper\UserHelper;
use Milex\CoreBundle\Model\FormModel as CommonFormModel;
use Milex\LeadBundle\Model\LeadModel;
use Milex\StageBundle\Entity\Stage;
use Milex\StageBundle\Event\StageBuilderEvent;
use Milex\StageBundle\Event\StageEvent;
use Milex\StageBundle\Form\Type\StageType;
use Milex\StageBundle\StageEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class StageModel.
 */
class StageModel extends CommonFormModel
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var UserHelper
     */
    protected $userHelper;

    public function __construct(LeadModel $leadModel, Session $session, UserHelper $userHelper)
    {
        $this->session    = $session;
        $this->leadModel  = $leadModel;
        $this->userHelper = $userHelper;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Milex\StageBundle\Entity\StageRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MilexStageBundle:Stage');
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionBase()
    {
        return 'stage:stages';
    }

    /**
     * {@inheritdoc}
     *
     * @throws MethodNotAllowedHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof Stage) {
            throw new MethodNotAllowedHttpException(['Stage']);
        }
        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create(StageType::class, $entity, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @return Stage|null
     */
    public function getEntity($id = null)
    {
        if (null === $id) {
            return new Stage();
        }

        return parent::getEntity($id);
    }

    /**
     * {@inheritdoc}
     *
     * @throws MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof Stage) {
            throw new MethodNotAllowedHttpException(['Stage']);
        }

        switch ($action) {
            case 'pre_save':
                $name = StageEvents::STAGE_PRE_SAVE;
                break;
            case 'post_save':
                $name = StageEvents::STAGE_POST_SAVE;
                break;
            case 'pre_delete':
                $name = StageEvents::STAGE_PRE_DELETE;
                break;
            case 'post_delete':
                $name = StageEvents::STAGE_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new StageEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }

            $this->dispatcher->dispatch($name, $event);

            return $event;
        }

        return null;
    }

    /**
     * Gets array of custom actions from bundles subscribed StageEvents::STAGE_ON_BUILD.
     *
     * @return mixed
     */
    public function getStageActions()
    {
        static $actions;

        if (empty($actions)) {
            //build them
            $actions = [];
            $event   = new StageBuilderEvent($this->translator);
            $this->dispatcher->dispatch(StageEvents::STAGE_ON_BUILD, $event);
            $actions['actions'] = $event->getActions();
            $actions['list']    = $event->getActionList();
            $actions['choices'] = $event->getActionChoices();
        }

        return $actions;
    }

    /**
     * Get line chart data of stages.
     *
     * @param char     $unit          {@link php.net/manual/en/function.date.php#refsect1-function.date-parameters}
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     * @param string   $dateFormat
     * @param array    $filter
     * @param bool     $canViewOthers
     *
     * @return array
     */
    public function getStageLineChartData($unit, \DateTime $dateFrom, \DateTime $dateTo, $dateFormat = null, $filter = [], $canViewOthers = true)
    {
        $chart = new LineChart($unit, $dateFrom, $dateTo, $dateFormat);
        $query = new ChartQuery($this->em->getConnection(), $dateFrom, $dateTo);
        $q     = $query->prepareTimeDataQuery('lead_stages_change_log', 'date_added', $filter);

        if (!$canViewOthers) {
            $q->join('t', MILEX_TABLE_PREFIX.'leads', 'l', 'l.id = t.lead_id')
                ->andWhere('l.owner_id = :userId')
                ->setParameter('userId', $this->userHelper->getUser()->getId());
        }

        $data = $query->loadAndBuildTimeData($q);
        $chart->setDataset($this->translator->trans('milex.stage.changes'), $data);

        return $chart->render();
    }

    /**
     * @return array
     */
    public function getUserStages()
    {
        $user = (!$this->security->isGranted('stage:stages:viewother')) ?
            $this->userHelper->getUser() : false;

        return $this->getRepository()->getStages($user);
    }
}
