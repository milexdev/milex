<?php

namespace Milex\ConfigBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ConfigFileType extends AbstractType
{
    public function getParent()
    {
        return FileType::class;
    }
}
