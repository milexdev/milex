<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Tests\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\CoreBundle\Translation\Translator;
use Milex\LeadBundle\Entity\Import;
use Milex\LeadBundle\Entity\LeadEventLog;
use Milex\LeadBundle\Entity\Tag;
use Milex\LeadBundle\Event\ImportInitEvent;
use Milex\LeadBundle\Event\ImportMappingEvent;
use Milex\LeadBundle\Event\ImportProcessEvent;
use Milex\LeadBundle\Event\ImportValidateEvent;
use Milex\LeadBundle\EventListener\ImportContactSubscriber;
use Milex\LeadBundle\Field\FieldList;
use Milex\LeadBundle\Model\LeadModel;
use PHPUnit\Framework\Assert;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

final class ImportContactSubscriberTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleValidateTags(): void
    {
        $tag = new Tag();
        $tag->setTag('tagLabel');

        $formMock = $this->createMock(Form::class);
        $formMock->method('getData')
            ->willReturn(
                [
                    'name' => 'Bud',
                    'tags' => new ArrayCollection([$tag]),
                ]
            );

        $event      = new ImportValidateEvent('contacts', $formMock);
        $subscriber = new ImportContactSubscriber(
            new class() extends FieldList {
                public function __construct()
                {
                }

                public function getFieldList(bool $byGroup = true, bool $alphabetical = true, array $filters = ['isPublished' => true, 'object' => 'lead']): array
                {
                    return [];
                }
            },
            $this->getCorePermissionsFake(),
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );

        $subscriber->onValidateImport($event);

        Assert::assertSame(['tagLabel'], $event->getTags());
        Assert::assertSame(['name' => 'Bud'], $event->getMatchedFields());
    }

    /**
     * @see https://github.com/milex/milex/issues/11080
     */
    public function testHandleFieldWithIntValues(): void
    {
        $formMock = $this->createMock(Form::class);
        $formMock->method('getData')
            ->willReturn(
                [
                    'name'           => 'Bud',
                    'skip_if_exists' => 1,
                ]
            );

        $event      = new ImportValidateEvent('contacts', $formMock);
        $subscriber = new ImportContactSubscriber(
            new class() extends FieldList {
                public function __construct()
                {
                }

                public function getFieldList(bool $byGroup = true, bool $alphabetical = true, array $filters = ['isPublished' => true, 'object' => 'lead']): array
                {
                    return [];
                }
            },
            $this->getCorePermissionsFake(),
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );

        $subscriber->onValidateImport($event);

        Assert::assertSame(['name' => 'Bud', 'skip_if_exists' => 1], $event->getMatchedFields());
    }

    public function testOnImportInitForUknownObject(): void
    {
        $subscriber = new ImportContactSubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportInitEvent('unicorn');
        $subscriber->onImportInit($event);
        Assert::assertFalse($event->objectSupported);
    }

    public function testOnImportInitForContactsObjectWithoutPermissions(): void
    {
        $subscriber = new ImportContactSubscriber(
            $this->getFieldListFake(),
            new class() extends CorePermissions {
                public function __construct()
                {
                }

                /**
                 * @param string $requestedPermission
                 */
                public function isGranted($requestedPermission, $mode = 'MATCH_ALL', $userEntity = null, $allowUnknown = false): bool
                {
                    Assert::assertSame('lead:imports:create', $requestedPermission);

                    return false;
                }
            },
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportInitEvent('contacts');
        $this->expectException(AccessDeniedException::class);
        $subscriber->onImportInit($event);
    }

    public function testOnImportInitForContactsObjectWithPermissions(): void
    {
        $subscriber = new ImportContactSubscriber(
            $this->getFieldListFake(),
            new class() extends CorePermissions {
                public function __construct()
                {
                }

                /**
                 * @param string $requestedPermission
                 */
                public function isGranted($requestedPermission, $mode = 'MATCH_ALL', $userEntity = null, $allowUnknown = false): bool
                {
                    Assert::assertSame('lead:imports:create', $requestedPermission);

                    return true;
                }
            },
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportInitEvent('contacts');
        $subscriber->onImportInit($event);
        Assert::assertTrue($event->objectSupported);
        Assert::assertSame('lead', $event->objectSingular);
        Assert::assertSame('milex.lead.leads', $event->objectName);
        Assert::assertSame('#milex_contact_index', $event->activeLink);
        Assert::assertSame('milex_contact_index', $event->indexRoute);
    }

    public function testOnFieldMappingForUnknownObject(): void
    {
        $subscriber = new ImportContactSubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportMappingEvent('unicorn');
        $subscriber->onFieldMapping($event);
        Assert::assertFalse($event->objectSupported);
    }

    public function testOnFieldMapping(): void
    {
        $subscriber = new ImportContactSubscriber(
            new class() extends FieldList {
                public function __construct()
                {
                }

                /**
                 * @param array<bool|string> $filters
                 *
                 * @return string[]
                 */
                public function getFieldList(bool $byGroup = true, bool $alphabetical = true, array $filters = ['isPublished' => true, 'object' => 'lead']): array
                {
                    return ['some fields'];
                }
            },
            $this->getCorePermissionsFake(),
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportMappingEvent('contacts');
        $subscriber->onFieldMapping($event);
        Assert::assertTrue($event->objectSupported);
        Assert::assertSame(
            [
                'milex.lead.contact' => [
                    'id' => 'milex.lead.import.label.id',
                    'some fields',
                ],
                'milex.lead.company' => [
                    'some fields',
                ],
                'milex.lead.special_fields' => [
                    'dateAdded'      => 'milex.lead.import.label.dateAdded',
                    'createdByUser'  => 'milex.lead.import.label.createdByUser',
                    'dateModified'   => 'milex.lead.import.label.dateModified',
                    'modifiedByUser' => 'milex.lead.import.label.modifiedByUser',
                    'lastActive'     => 'milex.lead.import.label.lastActive',
                    'dateIdentified' => 'milex.lead.import.label.dateIdentified',
                    'ip'             => 'milex.lead.import.label.ip',
                    'stage'          => 'milex.lead.import.label.stage',
                    'doNotEmail'     => 'milex.lead.import.label.doNotEmail',
                    'ownerusername'  => 'milex.lead.import.label.ownerusername',
                ],
            ],
            $event->fields
        );
    }

    public function testOnImportProcessForUnknownObject(): void
    {
        $subscriber = new ImportContactSubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            $this->getLeadModelFake(),
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
        $subscriber = new ImportContactSubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            new class() extends LeadModel {
                public function __construct()
                {
                }

                /**
                 * @param array<string> $fields
                 * @param array<string> $data
                 */
                public function import($fields, $data, $owner = null, $list = null, $tags = null, $persist = true, LeadEventLog $eventLog = null, $importId = null, $skipIfExists = false): bool
                {
                    return true;
                }
            },
            $this->getTranslatorFake()
        );
        $import = new Import();
        $import->setObject('lead');
        $event = new ImportProcessEvent($import, new LeadEventLog(), []);
        $subscriber->onImportProcess($event);
        Assert::assertTrue($event->wasMerged());
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

    private function getLeadModelFake(): LeadModel
    {
        return new class() extends LeadModel {
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
