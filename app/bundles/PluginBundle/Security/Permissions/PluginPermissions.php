<?php

namespace Milex\PluginBundle\Security\Permissions;

use Milex\CoreBundle\Security\Permissions\AbstractPermissions;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PluginPermissions.
 */
class PluginPermissions extends AbstractPermissions
{
    /**
     * {@inheritdoc}
     */
    public function __construct($params)
    {
        parent::__construct($params);
        $this->addManagePermission('plugins');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'plugin';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface &$builder, array $options, array $data)
    {
        $this->addManageFormFields('plugin', 'plugins', $builder, $data);
    }
}
