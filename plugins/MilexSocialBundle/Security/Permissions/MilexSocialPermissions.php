<?php

namespace MilexPlugin\MilexSocialBundle\Security\Permissions;

use Milex\CoreBundle\Security\Permissions\AbstractPermissions;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class MilexSocialPermissions.
 */
class MilexSocialPermissions extends AbstractPermissions
{
    /**
     * {@inheritdoc}
     */
    public function __construct($params)
    {
        parent::__construct($params);
        $this->addStandardPermissions('categories');
        $this->addStandardPermissions('monitoring');
        $this->addExtendedPermissions('tweets');
    }

    /**
     * {@inheritdoc}
     *
     * @return string|void
     */
    public function getName()
    {
        return 'milexSocial';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface &$builder, array $options, array $data)
    {
        $this->addStandardFormFields('milexSocial', 'categories', $builder, $data);
        $this->addStandardFormFields('milexSocial', 'monitoring', $builder, $data);
        $this->addExtendedFormFields('milexSocial', 'tweets', $builder, $data);
    }
}
