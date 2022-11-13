<?php

namespace Milex\EmailBundle\Stats\Helper;

use Milex\EmailBundle\Stats\FetchOptions\EmailStatOptions;
use Milex\LeadBundle\Entity\DoNotContact;
use Milex\StatsBundle\Aggregate\Collection\StatCollection;

class BouncedHelper extends AbstractHelper
{
    const NAME = 'email-bounced';

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @throws \Exception
     */
    public function generateStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options, StatCollection $statCollection)
    {
        $query = $this->getQuery($fromDateTime, $toDateTime);
        $q     = $query->prepareTimeDataQuery('lead_donotcontact', 'date_added');

        $q->andWhere('t.channel = :channel')
            ->setParameter('channel', 'email')
            ->andWhere($q->expr()->eq('t.reason', ':reason'))
            ->setParameter('reason', DoNotContact::BOUNCED);

        $this->limitQueryToEmailIds($q, $options->getEmailIds(), 'channel_id', 't');

        $q->join('t', MILEX_TABLE_PREFIX.'email_stats', 'es', 't.channel_id = es.email_id AND t.channel = "email" AND t.lead_id = es.lead_id');

        if (true === $options->canViewOthers()) {
            $this->limitQueryToCreator($q, 'es.email_id');
        }
        $this->addCompanyFilter($q, $options->getCompanyId());
        $this->addCampaignFilter($q, $options->getCampaignId(), 'es');
        $this->addSegmentFilter($q, $options->getSegmentId(), 'es');

        $this->fetchAndBindToCollection($q, $statCollection);
    }
}
