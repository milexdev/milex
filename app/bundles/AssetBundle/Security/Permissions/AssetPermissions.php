<?php

namespace Milex\AssetBundle\Security\Permissions;

use Milex\CoreBundle\Helper\CoreParametersHelper;
use Milex\CoreBundle\Security\Permissions\AbstractPermissions;
use Symfony\Component\Form\FormBuilderInterface;

class AssetPermissions extends AbstractPermissions
{
    public function __construct(CoreParametersHelper $coreParametersHelper)
    {
        parent::__construct($coreParametersHelper->all());
    }

    public function definePermissions()
    {
        $this->addExtendedPermissions('assets');
        $this->addStandardPermissions('categories');
    }

    public function getName()
    {
        return 'asset';
    }

    public function buildForm(FormBuilderInterface &$builder, array $options, array $data)
    {
        $this->addStandardFormFields('asset', 'categories', $builder, $data);
        $this->addExtendedFormFields('asset', 'assets', $builder, $data);
    }
}
