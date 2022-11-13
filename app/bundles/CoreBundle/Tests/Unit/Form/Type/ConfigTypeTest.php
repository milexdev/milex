<?php

namespace Milex\CoreBundle\Tests\Unit\Form\Type;

use Milex\CoreBundle\Factory\IpLookupFactory;
use Milex\CoreBundle\Form\Type\ConfigType;
use Milex\CoreBundle\Helper\LanguageHelper;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\PageBundle\Entity\PageRepository;
use Milex\PageBundle\Form\Type\PageListType;
use Milex\PageBundle\Model\PageModel;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validation;

class ConfigTypeTest extends TypeTestCase
{
    private $formBuilder;
    private $formType;

    protected function setUp(): void
    {
        $this->formBuilder = $this->createMock(FormBuilderInterface::class);
        $this->formType    = $this->getConfigFormType();
        parent::setUp();
    }

    public function testSubmitValidData()
    {
        $formData = [
            'site_url'             => 'http://example.com',
            'cache_path'           => 'tmp',
            'log_path'             => '/var/log',
            'image_path'           => '/tmp/sample-image.png',
            'cached_data_timeout'  => 30000,
            'date_format_full'     => 'F j, Y g:i:s a T',
            'date_format_short'    => 'D, M d - g:i:s a',
            'date_format_dateonly' => 'F j, Y',
            'date_format_timeonly' => 'g:i:s a',
        ];

        // $formData will retrieve data from the form submission; pass it as the second argument
        $form = $this->factory->create(ConfigType::class, $formData);

        // submit the data to the form directly
        $form->submit($formData);

        // This check ensures there are no transformation failures
        $this->assertTrue($form->isSynchronized());

        // check that $formData was modified as expected when the form was submitted
        $this->assertTrue($form->isValid());
    }

    private function getConfigFormType()
    {
        $translator      = $this->createMock(TranslatorInterface::class);
        $languageHelper  = $this->createMock(LanguageHelper::class);
        $ipLookupFactory = $this->createMock(IpLookupFactory::class);

        $languageHelper->expects($this->any())
                       ->method('fetchLanguages')
                       ->willReturn(['en' => ['name'=>'English']]);

        return new ConfigType($translator, $languageHelper, $ipLookupFactory, [], null);
    }

    protected function getExtensions()
    {
        $validator = Validation::createValidator();

        // or if you also need to read constraints from annotations
        $validator = Validation::createValidatorBuilder()
            ->getValidator();
        // create a type instance with the mocked dependencies
        $configType = $this->getConfigFormType();

        $repoMock = $this->createMock(PageRepository::class);
        $repoMock->expects($this->any())
                 ->method('getPageList')
                 ->willReturn([]);

        $pageModelMock = $this->createMock(PageModel::class);
        $pageModelMock->expects($this->any())
                      ->method('getRepository')
                      ->willReturn($repoMock);
        $permsMock    = $this->createMock(CorePermissions::class);
        $pageListType = new PageListType($pageModelMock, $permsMock);

        return [
            // register the type instances with the PreloadedExtension
            new ValidatorExtension($validator),
            new PreloadedExtension([$configType, $pageListType], []),
        ];
    }
}
