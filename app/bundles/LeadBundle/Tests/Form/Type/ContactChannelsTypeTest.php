<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Tests\Form\Type;

use Milex\CoreBundle\Test\AbstractMilexTestCase;
use Milex\LeadBundle\Form\Type\ContactChannelsType;
use PHPUnit\Framework\Assert;
use Symfony\Component\Form\FormInterface;

final class ContactChannelsTypeTest extends AbstractMilexTestCase
{
    protected function setUp(): void
    {
        $this->configParams['show_contact_pause_dates'] = true;
        parent::setUp();
    }

    public function testPauseDatesAreProperlyConfigured(): void
    {
        $form = $this->createForm(true);
        $this->assertOptions($form, 'contact_pause_start_date_email', true);
        $this->assertOptions($form, 'contact_pause_end_date_email', true);

        $form = $this->createForm(false);
        $this->assertOptions($form, 'contact_pause_start_date_email', false);
        $this->assertOptions($form, 'contact_pause_end_date_email', false);
    }

    /**
     * @param FormInterface<FormInterface> $form
     */
    private function assertOptions(FormInterface $form, string $name, bool $hasHtml5): void
    {
        $config = $form->get($name)->getConfig();
        Assert::assertSame($hasHtml5, $config->getOption('html5'));
        Assert::assertSame('yyyy-MM-dd', $config->getOption('format'));
    }

    /**
     * @return FormInterface<FormInterface>
     */
    private function createForm(bool $publicView): FormInterface
    {
        return self::$container->get('form.factory')->create(
            ContactChannelsType::class,
            null,
            [
                'channels'    => ['Email' => 'email'],
                'public_view' => $publicView,
            ]
        );
    }
}
