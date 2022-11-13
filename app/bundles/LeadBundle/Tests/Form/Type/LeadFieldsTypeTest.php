<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Tests\Form\Type;

use Milex\LeadBundle\Form\Type\LeadFieldsType;
use Milex\LeadBundle\Model\FieldModel;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LeadFieldsTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject|FieldModel
     */
    private $fieldModel;

    /**
     * @var LeadFieldsType
     */
    private $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fieldModel = $this->createMock(FieldModel::class);
        $this->form       = new LeadFieldsType($this->fieldModel);
    }

    public function testTransform(): void
    {
        /** @var MockObject|OptionsResolver $optionsResolver */
        $optionsResolver = $this->createMock(OptionsResolver::class);

        $this->fieldModel->expects($this->exactly(2))
            ->method('getFieldList')
            ->willReturnOnConsecutiveCalls(
                [
                    'Core' => [
                        'contact_field_1' => 'Contact field 1 label',
                    ],
                ],
                [
                    'company_field_1' => 'Company field 1 label',
                ]
            );

        // All options are set to true with this.
        $optionsResolver->method('offsetGet')
            ->willReturn(true);

        $optionsResolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->callback(
                function (array $defaults) use ($optionsResolver) {
                    $choices = $defaults['choices']($optionsResolver);

                    // Notice the labels and values are switched.
                    $this->assertSame(
                        [
                            'Core' => [
                              'Contact field 1 label'  => 'contact_field_1',
                              'milex.lead.field.tags' => 'tags',
                            ],
                            'Company' => [
                              'Company field 1 label' => 'company_field_1',
                            ],
                            'UTM' => [
                              'milex.lead.field.utmcampaign' => 'utm_campaign',
                              'milex.lead.field.utmcontent'  => 'utm_content',
                              'milex.lead.field.utmmedium'   => 'utm_medium',
                              'milex.lead.field.umtsource'   => 'utm_source',
                              'milex.lead.field.utmterm'     => 'utm_term',
                            ],
                        ],
                        $choices
                    );

                    return true;
                }
            ));

        $this->form->configureOptions($optionsResolver);
    }
}
