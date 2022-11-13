<?php

declare(strict_types=1);

namespace Milex\CoreBundle\Event;

use Milex\CoreBundle\Doctrine\GeneratedColumn\GeneratedColumn;
use Milex\CoreBundle\Doctrine\GeneratedColumn\GeneratedColumns;
use Symfony\Component\EventDispatcher\Event;

class GeneratedColumnsEvent extends Event
{
    /**
     * @var GeneratedColumns
     */
    private $generatedColumns;

    public function __construct()
    {
        $this->generatedColumns = new GeneratedColumns();
    }

    public function getGeneratedColumns(): GeneratedColumns
    {
        return $this->generatedColumns;
    }

    public function addGeneratedColumn(GeneratedColumn $generatedColumn): void
    {
        $this->generatedColumns->add($generatedColumn);
    }
}
