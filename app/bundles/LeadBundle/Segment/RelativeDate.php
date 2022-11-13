<?php

namespace Milex\LeadBundle\Segment;

use Symfony\Component\Translation\TranslatorInterface;

class RelativeDate
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function getRelativeDateStrings()
    {
        $keys = $this->getRelativeDateTranslationKeys();

        $strings = [];
        foreach ($keys as $key) {
            $strings[$key] = $this->translator->trans($key);
        }

        return $strings;
    }

    /**
     * @return array
     */
    private function getRelativeDateTranslationKeys()
    {
        return [
            'milex.lead.list.month_last',
            'milex.lead.list.month_next',
            'milex.lead.list.month_this',
            'milex.lead.list.today',
            'milex.lead.list.tomorrow',
            'milex.lead.list.yesterday',
            'milex.lead.list.week_last',
            'milex.lead.list.week_next',
            'milex.lead.list.week_this',
            'milex.lead.list.year_last',
            'milex.lead.list.year_next',
            'milex.lead.list.year_this',
            'milex.lead.list.anniversary',
        ];
    }
}
