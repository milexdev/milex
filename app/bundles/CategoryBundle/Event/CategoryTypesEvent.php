<?php

namespace Milex\CategoryBundle\Event;

use Milex\CategoryBundle\Entity\Category;
use Milex\CoreBundle\Event\CommonEvent;

/**
 * Class CategoryTypesEvent.
 */
class CategoryTypesEvent extends CommonEvent
{
    /**
     * @var array
     */
    protected $types = [];

    /**
     * Returns the array of Category Types.
     *
     * @return array
     */
    public function getCategoryTypes()
    {
        if (!array_key_exists('global', $this->types)) {
            // Alphabetize once
            asort($this->types);

            $this->types = array_merge(
                ['global' => 'milex.category.global'],
                $this->types
            );
        }

        return $this->types;
    }

    /**
     * Adds the category type and label.
     *
     * @param string $type
     * @param string $label
     */
    public function addCategoryType($type, $label = null)
    {
        if (is_int($type)) {
            $type = $label;
        }

        if (null === $label) {
            $label = 'milex.'.$type.'.'.$type;
        }

        $this->types[$type] = $label;
    }
}
