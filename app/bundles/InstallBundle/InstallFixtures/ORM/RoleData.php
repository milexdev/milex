<?php

namespace Milex\InstallBundle\InstallFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Milex\UserBundle\Entity\Role;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RoleData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface, FixtureGroupInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public static function getGroups(): array
    {
        return ['group_install', 'group_milex_install_data'];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        if ($this->hasReference('admin-role')) {
            return;
        }

        $translator = $this->container->get('translator');
        $role       = new Role();
        $role->setName($translator->trans('milex.user.role.admin.name', [], 'fixtures'));
        $role->setDescription($translator->trans('milex.user.role.admin.description', [], 'fixtures'));
        $role->setIsAdmin(1);
        $manager->persist($role);
        $manager->flush();

        $this->addReference('admin-role', $role);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
