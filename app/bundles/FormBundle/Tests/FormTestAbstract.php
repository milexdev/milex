<?php

namespace Milex\FormBundle\Tests;

use Doctrine\ORM\EntityManager;
use Milex\CampaignBundle\Membership\MembershipManager;
use Milex\CampaignBundle\Model\CampaignModel;
use Milex\CoreBundle\Doctrine\Helper\ColumnSchemaHelper;
use Milex\CoreBundle\Doctrine\Helper\TableSchemaHelper;
use Milex\CoreBundle\Entity\IpAddress;
use Milex\CoreBundle\Helper\IpLookupHelper;
use Milex\CoreBundle\Helper\TemplatingHelper;
use Milex\CoreBundle\Helper\ThemeHelperInterface;
use Milex\CoreBundle\Helper\UserHelper;
use Milex\CoreBundle\Templating\Helper\DateHelper;
use Milex\CoreBundle\Translation\Translator;
use Milex\FormBundle\Entity\FormRepository;
use Milex\FormBundle\Event\Service\FieldValueTransformer;
use Milex\FormBundle\Helper\FormFieldHelper;
use Milex\FormBundle\Helper\FormUploader;
use Milex\FormBundle\Model\ActionModel;
use Milex\FormBundle\Model\FieldModel;
use Milex\FormBundle\Model\FormModel;
use Milex\FormBundle\Model\SubmissionModel;
use Milex\FormBundle\Validator\UploadFieldValidator;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Entity\LeadRepository;
use Milex\LeadBundle\Model\CompanyModel;
use Milex\LeadBundle\Model\FieldModel as LeadFieldModel;
use Milex\LeadBundle\Model\LeadModel;
use Milex\LeadBundle\Tracker\ContactTracker;
use Milex\LeadBundle\Tracker\Service\DeviceTrackingService\DeviceTrackingServiceInterface;
use Milex\PageBundle\Model\PageModel;
use Milex\UserBundle\Entity\User;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;

class FormTestAbstract extends TestCase
{
    protected static $mockId   = 123;
    protected static $mockName = 'Mock test name';
    protected $mockTrackingId;
    protected $formRepository;
    protected $leadFieldModel;

    protected function setUp(): void
    {
        $this->mockTrackingId = hash('sha1', uniqid((string) mt_rand()));
    }

    /**
     * @return FormModel
     */
    protected function getFormModel()
    {
        $requestStack         = $this->createMock(RequestStack::class);
        $templatingHelperMock = $this->createMock(TemplatingHelper::class);
        $themeHelper          = $this->createMock(ThemeHelperInterface::class);
        $formActionModel      = $this->createMock(ActionModel::class);
        $formFieldModel       = $this->createMock(FieldModel::class);
        $fieldHelper          = $this->createMock(FormFieldHelper::class);
        $dispatcher           = $this->createMock(EventDispatcher::class);
        $translator           = $this->createMock(Translator::class);
        $entityManager        = $this->createMock(EntityManager::class);
        $formUploaderMock     = $this->createMock(FormUploader::class);
        $contactTracker       = $this->createMock(ContactTracker::class);
        $this->leadFieldModel = $this->createMock(LeadFieldModel::class);
        $this->formRepository = $this->createMock(FormRepository::class);
        $columnSchemaHelper   = $this->createMock(ColumnSchemaHelper::class);
        $tableSchemaHelper    = $this->createMock(TableSchemaHelper::class);

        $contactTracker->expects($this
            ->any())
            ->method('getContact')
            ->willReturn($this
                ->returnValue(['id' => self::$mockId, 'name' => self::$mockName])
            );

        $templatingHelperMock->expects($this
            ->any())
            ->method('getTemplating')
            ->willReturn($this->createMock(EngineInterface::class));

        $entityManager->expects($this
            ->any())
            ->method('getRepository')
            ->will(
                $this->returnValueMap(
                    [
                        ['MilexFormBundle:Form', $this->formRepository],
                    ]
                )
            );

        $formModel = new FormModel(
            $requestStack,
            $templatingHelperMock,
            $themeHelper,
            $formActionModel,
            $formFieldModel,
            $fieldHelper,
            $this->leadFieldModel,
            $formUploaderMock,
            $contactTracker,
            $columnSchemaHelper,
            $tableSchemaHelper
        );

        $formModel->setDispatcher($dispatcher);
        $formModel->setTranslator($translator);
        $formModel->setEntityManager($entityManager);

        return $formModel;
    }

    /**
     * @return SubmissionModel
     */
    protected function getSubmissionModel()
    {
        $ipLookupHelper           = $this->createMock(IpLookupHelper::class);
        $templatingHelperMock     = $this->createMock(TemplatingHelper::class);
        $formModel                = $this->createMock(FormModel::class);
        $pageModel                = $this->createMock(PageModel::class);
        $leadModel                = $this->createMock(LeadModel::class);
        $campaignModel            = $this->createMock(CampaignModel::class);
        $membershipManager        = $this->createMock(MembershipManager::class);
        $leadFieldModel           = $this->createMock(LeadFieldModel::class);
        $companyModel             = $this->createMock(CompanyModel::class);
        $fieldHelper              = $this->createMock(FormFieldHelper::class);
        $dispatcher               = $this->createMock(EventDispatcher::class);
        $translator               = $this->createMock(Translator::class);
        $dateHelper               = $this->createMock(DateHelper::class);
        $contactTracker           = $this->createMock(ContactTracker::class);
        $userHelper               = $this->createMock(UserHelper::class);
        $entityManager            = $this->createMock(EntityManager::class);
        $formRepository           = $this->createMock(FormRepository::class);
        $leadRepository           = $this->createMock(LeadRepository::class);
        $mockLogger               = $this->createMock(Logger::class);
        $uploadFieldValidatorMock = $this->createMock(UploadFieldValidator::class);
        $formUploaderMock         = $this->createMock(FormUploader::class);
        $deviceTrackingService    = $this->createMock(DeviceTrackingServiceInterface::class);
        $file1Mock                = $this->createMock(UploadedFile::class);
        $router                   = $this->createMock(RouterInterface::class);
        $router->method('generate')->willReturn('absolute/path/somefile.jpg');

        $lead                     = new Lead();
        $lead->setId(123);

        $leadFieldModel->expects($this->any())
            ->method('getUniqueIdentifierFields')
            ->willReturn(['eyJpc1B1Ymxpc2hlZCI6dHJ1ZSwiaXNVbmlxdWVJZGVudGlmZXIiOnRydWUsIm9iamVjdCI6ImxlYWQifQ==' => ['email' => 'Email']]);

        $contactTracker->expects($this
            ->any())
            ->method('getContact')
            ->willReturn($lead);

        $userHelper->expects($this->any())
            ->method('getUser')
            ->willReturn(new User());

        $mockLeadField['email'] = [
                'label'        => 'Email',
                'alias'        => 'email',
                'type'         => 'email',
                'group'        => 'core',
                'group_label'  => 'Core',
                'defaultValue' => '',
                'properties'   => [],
            ];

        $leadFieldModel->expects($this->any())
            ->method('getFieldListWithProperties')
            ->willReturn($mockLeadField);

        $entityManager->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnValueMap(
                    [
                        ['MilexLeadBundle:Lead', $leadRepository],
                        ['MilexFormBundle:Submission', $formRepository],
                    ]
                )
            );

        $leadRepository->expects($this->any())
            ->method('getLeadsByUniqueFields')
            ->willReturn(null);

        $file1Mock->expects($this->any())
            ->method('getClientOriginalName')
            ->willReturn('test.jpg');

        $uploadFieldValidatorMock->expects($this->any())
            ->method('processFileValidation')
            ->willReturn($file1Mock);

        $ipLookupHelper->expects($this->any())
            ->method('getIpAddress')
            ->willReturn(new IpAddress());

        $companyModel->expects($this->any())
            ->method('fetchCompanyFields')
            ->willReturn([]);
        $submissionModel = new SubmissionModel(
            $ipLookupHelper,
            $templatingHelperMock,
            $formModel,
            $pageModel,
            $leadModel,
            $campaignModel,
            $membershipManager,
            $leadFieldModel,
            $companyModel,
            $fieldHelper,
            $uploadFieldValidatorMock,
            $formUploaderMock,
            $deviceTrackingService,
            new FieldValueTransformer($router),
            $dateHelper,
            $contactTracker
        );
        $submissionModel->setDispatcher($dispatcher);
        $submissionModel->setTranslator($translator);
        $submissionModel->setEntityManager($entityManager);
        $submissionModel->setUserHelper($userHelper);
        $submissionModel->setLogger($mockLogger);

        return $submissionModel;
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function getTestFormFields(): array
    {
        $fieldSession          = 'milex_'.sha1(uniqid((string) mt_rand(), true));
        $fieldSession2         = 'milex_'.sha1(uniqid((string) mt_rand(), true));
        $fields[$fieldSession] = [
            'label'        => 'Email',
            'showLabel'    => 1,
            'saveResult'   => 1,
            'defaultValue' => false,
            'alias'        => 'email',
            'type'         => 'email',
            'leadField'    => 'email',
            'id'           => $fieldSession,
        ];

        $fields['file'] = [
            'label'                   => 'File',
            'showLabel'               => 1,
            'saveResult'              => 1,
            'defaultValue'            => false,
            'alias'                   => 'file',
            'type'                    => 'file',
            'id'                      => 'file',
            'allowed_file_size'       => 1,
            'allowed_file_extensions' => ['jpg', 'gif'],
        ];

        $fields['123'] = [
            'label'        => 'Parent Field',
            'showLabel'    => 1,
            'saveResult'   => 1,
            'defaultValue' => false,
            'alias'        => 'parent',
            'type'         => 'select',
            'id'           => '123',
        ];

        $fields['456'] = [
            'label'        => 'Child',
            'showLabel'    => 1,
            'saveResult'   => 1,
            'defaultValue' => false,
            'alias'        => 'child',
            'type'         => 'text',
            'id'           => '456',
            'parent'       => '123',
        ];

        $fields[$fieldSession2] = [
            'label'        => 'New Child',
            'showLabel'    => 1,
            'saveResult'   => 1,
            'defaultValue' => false,
            'alias'        => 'new_child',
            'type'         => 'text',
            'id'           => $fieldSession2,
            'parent'       => '123',
        ];

        return $fields;
    }
}
