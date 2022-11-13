<?php

namespace Milex\ReportBundle\Security\Permissions;

use Milex\CoreBundle\Security\Permissions\AbstractPermissions;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ReportPermissions.
 */
class ReportPermissions extends AbstractPermissions
{
    /**
     * {@inheritdoc}
     */
    public function __construct($params)
    {
        parent::__construct($params);
        $this->addExtendedPermissions('reports');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'report';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface &$builder, array $options, array $data)
    {
        $this->addExtendedFormFields('report', 'reports', $builder, $data);
    }
}
