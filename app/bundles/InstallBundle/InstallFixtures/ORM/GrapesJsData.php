<?php

declare(strict_types=1);

namespace Milex\InstallBundle\InstallFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Milex\PluginBundle\Entity\Integration;
use Milex\PluginBundle\Entity\Plugin;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GrapesJsData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface, FixtureGroupInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public static function getGroups(): array
    {
        return ['group_install', 'group_milex_install_data'];
    }

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager): void
    {
        $projectDir               = $this->container->get('kernel')->getProjectDir();
        $grapeJsBuilderConfigPath = $projectDir.'/plugins/GrapesJsBuilderBundle/Config/config.php';

        if (!file_exists($grapeJsBuilderConfigPath)) {
            return;
        }

        $parameters = include $grapeJsBuilderConfigPath;

        if (!is_array($parameters)) {
            return;
        }

        $plugin = new Plugin();
        $plugin->setName($parameters['name']);
        $plugin->setDescription($parameters['description']);
        $plugin->setVersion($parameters['version']);
        $plugin->setAuthor($parameters['author']);
        $plugin->setBundle('GrapesJsBuilderBundle');
        $manager->persist($plugin);

        $integration = new Integration();
        $integration->setIsPublished(true);
        $integration->setName('GrapesJsBuilder');
        $integration->setPlugin($plugin);
        $manager->persist($integration);

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1;
    }
}
