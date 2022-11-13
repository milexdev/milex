<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Tests\EventListener;

use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\LeadBundle\Entity\Import;
use Milex\LeadBundle\Entity\LeadEventLog;
use Milex\LeadBundle\Event\ImportInitEvent;
use Milex\LeadBundle\Event\ImportMappingEvent;
use Milex\LeadBundle\Event\ImportProcessEvent;
use Milex\LeadBundle\Event\ImportValidateEvent;
use Milex\LeadBundle\EventListener\ImportCompanySubscriber;
use Milex\LeadBundle\Field\FieldList;
use Milex\LeadBundle\Model\CompanyModel;
use PHPUnit\Framework\Assert;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

final class ImportCompanySubscriberTest extends \PHPUnit\Framework\TestCase
{
    public function testOnImportInitForUknownObject(): void
    {
        $subscriber = new ImportCompanySubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            $this->getCompanyModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportInitEvent('unicorn');
        $subscriber->onImportInit($event);
        Assert::assertFalse($event->objectSupported);
    }

    public function testOnImportInitForContactsObjectWithoutPermissions(): void
    {
        $subscriber = new ImportCompanySubscriber(
            $this->getFieldListFake(),
            new class() extends CorePermissions {
                public function __construct()
                {
                }

                /**
                 * @param array<mixed> $requestedPermission
                 */
                public function isGranted($requestedPermission, $mode = 'MATCH_ALL', $userEntity = null, $allowUnknown = false)
                {
                    Assert::assertSame('lead:imports:create', $requestedPermission);

                    return false;
                }
            },
            $this->getCompanyModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportInitEvent('companies');
        $this->expectException(AccessDeniedException::class);
        $subscriber->onImportInit($event);
    }

    public function testOnImportInitForContactsObjectWithPermissions(): void
    {
        $subscriber = new ImportCompanySubscriber(
            $this->getFieldListFake(),
            new class() extends CorePermissions {
                public function __construct()
                {
                }

                /**
                 * @param array<mixed> $requestedPermission
                 */
                public function isGranted($requestedPermission, $mode = 'MATCH_ALL', $userEntity = null, $allowUnknown = false)
                {
                    Assert::assertSame('lead:imports:create', $requestedPermission);

                    return true;
                }
            },
            $this->getCompanyModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportInitEvent('companies');
        $subscriber->onImportInit($event);
        Assert::assertTrue($event->objectSupported);
        Assert::assertSame('company', $event->objectSingular);
        Assert::assertSame('milex.lead.lead.companies', $event->objectName);
        Assert::assertSame('#milex_company_index', $event->activeLink);
        Assert::assertSame('milex_company_index', $event->indexRoute);
    }

    public function testOnFieldMappingForUnknownObject(): void
    {
        $subscriber = new ImportCompanySubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            $this->getCompanyModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportMappingEvent('unicorn');
        $subscriber->onFieldMapping($event);
        Assert::assertFalse($event->objectSupported);
    }

    public function testOnFieldMapping(): void
    {
        $subscriber = new ImportCompanySubscriber(
            new class() extends FieldList {
                public function __construct()
                {
                }

                /**
                 * @param array<string, mixed> $filters
                 *
                 * @return array<string>
                 */
                public function getFieldList(bool $byGroup = true, bool $alphabetical = true, array $filters = ['isPublished' => true, 'object' => 'lead']): array
                {
                    return ['some fields'];
                }
            },
            $this->getCorePermissionsFake(),
            $this->getCompanyModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportMappingEvent('companies');
        $subscriber->onFieldMapping($event);
        Assert::assertTrue($event->objectSupported);
        Assert::assertSame(
            [
                'milex.lead.company' => [
                    'some fields',
                ],
                'milex.lead.special_fields' => [
                    'dateAdded'      => 'milex.lead.import.label.dateAdded',
                    'createdByUser'  => 'milex.lead.import.label.createdByUser',
                    'dateModified'   => 'milex.lead.import.label.dateModified',
                    'modifiedByUser' => 'milex.lead.import.label.modifiedByUser',
                ],
            ],
            $event->fields
        );
    }

    public function testOnImportProcessForUnknownObject(): void
    {
        $subscriber = new ImportCompanySubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            $this->getCompanyModelFake(),
            $this->getTranslatorFake()
        );
        $import = new Import();
        $import->setObject('unicorn');
        $event = new ImportProcessEvent($import, new LeadEventLog(), []);
        $subscriber->onImportProcess($event);
        $this->expectException(\UnexpectedValueException::class);
        $event->wasMerged();
    }

    public function testOnImportProcessForKnownObject(): void
    {
        $subscriber = new ImportCompanySubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            new class() extends CompanyModel {
                public function __construct()
                {
                }

                /**
                 * @param array<mixed> $fields
                 * @param array<mixed> $data
                 */
                public function import($fields, $data, $owner = null, $skipIfExists = false)
                {
                    return true;
                }
            },
            $this->getTranslatorFake()
        );
        $import = new Import();
        $import->setObject('company');
        $event = new ImportProcessEvent($import, new LeadEventLog(), []);
        $subscriber->onImportProcess($event);
        Assert::assertTrue($event->wasMerged());
    }

    public function testImportCompanySubscriberDoesHaveTranslatorInitialized(): void
    {
        $fieldListMock         = $this->createMock(FieldList::class);
        $missingRequiredFields = ['Company Name'];
        $matchedFields         = ['Company Email'];
        $fieldListMock->expects($this->once())
            ->method('getFieldList')
            ->with(false, false, [
                'isPublished' => true,
                'object'      => 'company',
                'isRequired'  => true,
            ])
            ->willReturn($missingRequiredFields);
        $translatorInterfaceMock = $this->createMock(TranslatorInterface::class);
        $subscriber              = new ImportCompanySubscriber(
            $fieldListMock,
            $this->getCorePermissionsFake(),
            $this->getCompanyModelFake(),
            $translatorInterfaceMock
        );
        $importValidateEventMock = $this->createMock(ImportValidateEvent::class);
        $importValidateEventMock->expects($this->once())
            ->method('importIsForRouteObject')
            ->with('companies')
            ->willReturn(true);
        $formMock = $this->createMock(Form::class);
        $importValidateEventMock->expects($this->exactly(2))
            ->method('getForm')
            ->willReturn($formMock);
        $formMock->expects($this->once())
            ->method('getData')
            ->willReturnOnConsecutiveCalls($matchedFields);
        $translatorInterfaceMock->expects($this->once())
            ->method('trans')
            ->with(
                'milex.import.missing.required.fields',
                [
                    '%requiredFields%' => implode(', ', $missingRequiredFields),
                    '%fieldOrFields%'  => 'field',
                ],
                'validators'
            );

        $subscriber->onValidateImport($importValidateEventMock);
    }

    private function getFieldListFake(): FieldList
    {
        return new class() extends FieldList {
            public function __construct()
            {
            }
        };
    }

    private function getCorePermissionsFake(): CorePermissions
    {
        return new class() extends CorePermissions {
            public function __construct()
            {
            }
        };
    }

    private function getCompanyModelFake(): CompanyModel
    {
        return new class() extends CompanyModel {
            public function __construct()
            {
            }
        };
    }

    private function getTranslatorFake(): TranslatorInterface
    {
        return new class() extends Translator {
            public function __construct()
            {
            }
        };
    }
}
