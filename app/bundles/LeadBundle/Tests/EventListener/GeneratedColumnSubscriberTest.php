<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Tests\EventListener;

use Milex\CoreBundle\Doctrine\GeneratedColumn\GeneratedColumn;
use Milex\CoreBundle\Event\GeneratedColumnsEvent;
use Milex\LeadBundle\Event\LeadListFiltersChoicesEvent;
use Milex\LeadBundle\EventListener\GeneratedColumnSubscriber;
use Milex\LeadBundle\Model\ListModel;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class GeneratedColumnSubscriberTest extends TestCase
{
    /**
     * @var MockObject&TranslatorInterface
     */
    private $translator;

    private GeneratedColumnSubscriber $generatedColumnSubscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $segmentModel = new class() extends ListModel {
            public function __construct()
            {
            }
        };

        $this->translator                = $this->createMock(TranslatorInterface::class);
        $this->generatedColumnSubscriber = new GeneratedColumnSubscriber($segmentModel, $this->translator);
    }

    public function testInGeneratedColumnsBuild(): void
    {
        $event = new GeneratedColumnsEvent();

        $this->generatedColumnSubscriber->onGeneratedColumnsBuild($event);

        /** @var GeneratedColumn $generatedColumn */
        $generatedColumn = $event->getGeneratedColumns()->current();

        Assert::assertSame(MILEX_TABLE_PREFIX.'leads', $generatedColumn->getTableName());
        Assert::assertSame('generated_email_domain', $generatedColumn->getColumnName());
        Assert::assertSame('VARCHAR(255) AS (SUBSTRING(email, LOCATE("@", email) + 1)) COMMENT \'(DC2Type:generated)\'', $generatedColumn->getColumnDefinition());
    }

    public function testOnGenerateSegmentFilters(): void
    {
        $event = new LeadListFiltersChoicesEvent(
            [],
            [],
            $this->translator,
            new Request()
        );

        $this->translator->method('trans')
            ->with('milex.email.segment.choice.generated_email_domain')
            ->willReturn('translated string');

        $this->generatedColumnSubscriber->onGenerateSegmentFilters($event);

        Assert::assertSame(
            [
                'label'      => 'translated string',
                'properties' => ['type' => 'text'],
                'operators'  => [
                    'milex.lead.list.form.operator.equals'     => '=',
                    'milex.lead.list.form.operator.notequals'  => '!=',
                    'milex.lead.list.form.operator.isempty'    => 'empty',
                    'milex.lead.list.form.operator.isnotempty' => '!empty',
                    'milex.lead.list.form.operator.islike'     => 'like',
                    'milex.lead.list.form.operator.isnotlike'  => '!like',
                    'milex.lead.list.form.operator.regexp'     => 'regexp',
                    'milex.lead.list.form.operator.notregexp'  => '!regexp',
                    'milex.core.operator.starts.with'          => 'startsWith',
                    'milex.core.operator.ends.with'            => 'endsWith',
                    'milex.core.operator.contains'             => 'contains',
                ],
                'object' => 'lead',
            ],
            $event->getChoices()['lead']['generated_email_domain']
        );
    }
}
