<?php

declare(strict_types=1);

namespace Milex\ReportBundle\Tests\Helper;

use Milex\ReportBundle\Helper\ReportHelper;
use PHPUnit\Framework\TestCase;

final class ReportHelperTest extends TestCase
{
    /**
     * @var ReportHelper
     */
    private $reportHelper;

    protected function setUp(): void
    {
        $this->reportHelper = new ReportHelper();
    }

    public function testGetStandardColumnsMethodReturnsCorrectColumns(): void
    {
        $columns = $this->reportHelper->getStandardColumns('somePrefix');

        $expectedColumnns = [
            'somePrefixid' => [
                    'label' => 'milex.core.id',
                    'type'  => 'int',
                    'alias' => 'somePrefixid',
                ],
            'somePrefixname' => [
                    'label' => 'milex.core.name',
                    'type'  => 'string',
                    'alias' => 'somePrefixname',
                ],
            'somePrefixcreated_by_user' => [
                    'label' => 'milex.core.createdby',
                    'type'  => 'string',
                    'alias' => 'somePrefixcreated_by_user',
                ],
            'somePrefixdate_added' => [
                    'label' => 'milex.report.field.date_added',
                    'type'  => 'datetime',
                    'alias' => 'somePrefixdate_added',
                ],
            'somePrefixmodified_by_user' => [
                    'label' => 'milex.report.field.modified_by_user',
                    'type'  => 'string',
                    'alias' => 'somePrefixmodified_by_user',
                ],
            'somePrefixdate_modified' => [
                    'label' => 'milex.report.field.date_modified',
                    'type'  => 'datetime',
                    'alias' => 'somePrefixdate_modified',
                ],
            'somePrefixdescription' => [
                    'label' => 'milex.core.description',
                    'type'  => 'string',
                    'alias' => 'somePrefixdescription',
                ],
            'somePrefixpublish_up' => [
                    'label' => 'milex.report.field.publish_up',
                    'type'  => 'datetime',
                    'alias' => 'somePrefixpublish_up',
                ],
            'somePrefixpublish_down' => [
                    'label' => 'milex.report.field.publish_down',
                    'type'  => 'datetime',
                    'alias' => 'somePrefixpublish_down',
                ],
            'somePrefixis_published' => [
                    'label' => 'milex.report.field.is_published',
                    'type'  => 'bool',
                    'alias' => 'somePrefixis_published',
                ],
        ];

        $this->assertEquals($expectedColumnns, $columns);
    }
}
