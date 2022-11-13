<?php

declare(strict_types=1);

namespace Milex\FormBundle\Tests\Model;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\Doctrine\Helper\ColumnSchemaHelper;
use Milex\CoreBundle\Doctrine\Helper\TableSchemaHelper;
use Milex\CoreBundle\Helper\TemplatingHelper;
use Milex\CoreBundle\Helper\ThemeHelperInterface;
use Milex\FormBundle\Entity\Form;
use Milex\FormBundle\Entity\FormRepository;
use Milex\FormBundle\Helper\FormFieldHelper;
use Milex\FormBundle\Helper\FormUploader;
use Milex\FormBundle\Model\ActionModel;
use Milex\FormBundle\Model\FieldModel;
use Milex\FormBundle\Model\FormModel;
use Milex\FormBundle\Tests\FormTestAbstract;
use Milex\LeadBundle\Model\FieldModel as LeadFieldModel;
use Milex\LeadBundle\Tracker\ContactTracker;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class DeleteFormTest extends FormTestAbstract
{
    public function testDelete(): void
    {
        $requestStack         = $this->createMock(RequestStack::class);
        $templatingHelperMock = $this->createMock(TemplatingHelper::class);
        $themeHelper          = $this->createMock(ThemeHelperInterface::class);
        $formActionModel      = $this->createMock(ActionModel::class);
        $formFieldModel       = $this->createMock(FieldModel::class);
        $fieldHelper          = $this->createMock(FormFieldHelper::class);
        $leadFieldModel       = $this->createMock(LeadFieldModel::class);
        $formUploaderMock     = $this->createMock(FormUploader::class);
        $contactTracker       = $this->createMock(ContactTracker::class);
        $columnSchemaHelper   = $this->createMock(ColumnSchemaHelper::class);
        $tableSchemaHelper    = $this->createMock(TableSchemaHelper::class);
        $entityManager        = $this->createMock(EntityManager::class);
        $dispatcher           = $this->createMock(EventDispatcher::class);
        $formRepository       = $this->createMock(FormRepository::class);
        $form                 = $this->createMock(Form::class);
        $formModel            = new FormModel(
            $requestStack,
            $templatingHelperMock,
            $themeHelper,
            $formActionModel,
            $formFieldModel,
            $fieldHelper,
            $leadFieldModel,
            $formUploaderMock,
            $contactTracker,
            $columnSchemaHelper,
            $tableSchemaHelper
        );

        $dispatcher->expects($this->exactly(2))
            ->method('hasListeners')
            ->withConsecutive(['milex.form_pre_delete'], ['milex.form_post_delete'])
            ->willReturn(false);

        $entityManager->expects($this->once())
            ->method('getRepository')
            ->willReturn($formRepository);

        $formModel->setDispatcher($dispatcher);
        $formModel->setEntityManager($entityManager);

        $form->expects($this->exactly(2))
            ->method('getId')
            ->with()
            ->willReturn(1);

        $formUploaderMock->expects($this->once())
            ->method('deleteFilesOfForm')
            ->with($form);

        $formRepository->expects($this->once())
            ->method('deleteEntity')
            ->with($form);

        $formModel->deleteEntity($form);

        $this->assertSame(1, $form->deletedId);
    }
}
