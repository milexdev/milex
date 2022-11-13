<?php

namespace Milex\CategoryBundle\Event;

use Milex\CategoryBundle\Entity\Category;
use Milex\CoreBundle\Event\CommonEvent;

/**
 * Class CategoryEvent.
 */
class CategoryEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Category &$category, $isNew = false)
    {
        $this->entity = &$category;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Category entity.
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->entity;
    }

    /**
     * Sets the Category entity.
     */
    public function setCategory(Category $category)
    {
        $this->entity = $category;
    }
}
