<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Entity\Tag;

class LoadTagData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $tag = new Tag('Tag A');
        $manager->persist($tag);
        $manager->flush();

        $contact1 = $this->getReference('lead-1');
        \assert($contact1 instanceof Lead);
        $contact1->addTag($tag);

        $contact3 = $this->getReference('lead-3');
        \assert($contact1 instanceof Lead);
        $contact3->addTag($tag);

        $manager->persist($contact1);
        $manager->persist($contact3);
        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 8;
    }
}
