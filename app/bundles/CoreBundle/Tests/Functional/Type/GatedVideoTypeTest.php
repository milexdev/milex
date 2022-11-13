<?php

declare(strict_types=1);

namespace Milex\CoreBundle\Tests\Functional\Type;

use Milex\CoreBundle\Form\Type\GatedVideoType;
use Milex\CoreBundle\Test\MilexMysqlTestCase;
use Milex\FormBundle\Entity\Form;
use PHPUnit\Framework\Assert;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\FormFactory;

class GatedVideoTypeTest extends MilexMysqlTestCase
{
    public function testFormSelect(): void
    {
        $this->prepareForm('published1', true);
        $this->prepareForm('unpublished1', false);
        $this->prepareForm('published2', true);

        $this->em->flush();

        /** @var FormFactory $formFactory */
        $formFactory = self::$container->get('form.factory');

        $form            = $formFactory->create(GatedVideoType::class);
        $formChoiceField = $form->get('formid');
        $attributes      = $formChoiceField->getConfig()->getAttributes();

        /** @var ArrayChoiceList $choiceList */
        $choiceList = $attributes['choice_list'];

        // There should be 2 choices - the 2 published forms.
        Assert::assertCount(2, $choiceList->getChoices());
    }

    private function prepareForm(string $alias, bool $published): Form
    {
        $form = new Form();
        $form->setName($alias);
        $form->setAlias($alias);
        $form->setIsPublished($published);
        $this->em->persist($form);

        return $form;
    }
}
