<?php

namespace MilexPlugin\MilexTagManagerBundle\Tests\Functional\Entity;

use Milex\CoreBundle\Test\MilexMysqlTestCase;
use Milex\LeadBundle\Entity\Tag;
use MilexPlugin\MilexTagManagerBundle\Entity\TagRepository;
use MilexPlugin\MilexTagManagerBundle\Model\TagModel;
use PHPUnit\Framework\Assert;

class TagRepositoryTest extends MilexMysqlTestCase
{
    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @var TagModel
     */
    private $tagModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tagRepository = self::$container->get('milex.tagmanager.repository.tag');
        $this->tagModel      = self::$container->get('milex.tagmanager.repository.tag');

        $tags = [
            'tag1',
            'tag2',
            'tag3',
            'tag4',
        ];

        foreach ($tags as $tagName) {
            $tag = new Tag();
            $tag->setTag($tagName);
            $this->tagModel->saveEntity($tag);
        }
    }

    public function testCountOccurencesReturnsCorrectQuantityOfTags(): void
    {
        $count = $this->tagRepository->countOccurrences('tag2');
        Assert::assertSame(1, $count);
    }
}
