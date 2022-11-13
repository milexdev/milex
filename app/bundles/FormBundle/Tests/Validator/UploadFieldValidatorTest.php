<?php

namespace Milex\FormBundle\Tests\Validator;

use Milex\CoreBundle\Exception\FileInvalidException;
use Milex\CoreBundle\Validator\FileUploadValidator;
use Milex\FormBundle\Entity\Field;
use Milex\FormBundle\Exception\FileValidationException;
use Milex\FormBundle\Exception\NoFileGivenException;
use Milex\FormBundle\Validator\UploadFieldValidator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class UploadFieldValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testdox No Files given
     *
     * @covers \Milex\FormBundle\Validator\UploadFieldValidator::processFileValidation
     */
    public function testNoFilesGiven()
    {
        $fileUploadValidatorMock = $this->getMockBuilder(FileUploadValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileUploadValidatorMock->expects($this->never())
            ->method('validate');

        $parameterBagMock = $this->getMockBuilder(ParameterBag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $parameterBagMock->expects($this->once())
            ->method('get')
            ->with('milexform')
            ->willReturn(false);

        $request        = new Request();
        $request->files = $parameterBagMock;

        $fileUploadValidator = new UploadFieldValidator($fileUploadValidatorMock);

        $field = new Field();

        $this->expectException(NoFileGivenException::class);

        $fileUploadValidator->processFileValidation($field, $request);
    }

    /**
     * @testdox Exception should be thrown when validation fails
     *
     * @covers \Milex\FormBundle\Validator\UploadFieldValidator::processFileValidation
     */
    public function testValidationFailed()
    {
        $fileUploadValidatorMock = $this->getMockBuilder(FileUploadValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileUploadValidatorMock->expects($this->once())
            ->method('validate')
            ->willThrowException(new FileInvalidException('Validation failed'));

        $parameterBagMock = $this->getMockBuilder(ParameterBag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileMock = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $files = [
            'file' => $fileMock,
        ];

        $parameterBagMock->expects($this->once())
            ->method('get')
            ->with('milexform')
            ->willReturn($files);

        $request        = new Request();
        $request->files = $parameterBagMock;

        $fileUploadValidator = new UploadFieldValidator($fileUploadValidatorMock);

        $field = new Field();
        $field->setAlias('file');
        $field->setProperties([
            'allowed_file_size'       => 1,
            'allowed_file_extensions' => ['jpg', 'gif'],
        ]);

        $this->expectException(FileValidationException::class);
        $this->expectExceptionMessage('Validation failed');

        $fileUploadValidator->processFileValidation($field, $request);
    }

    /**
     * @testdox No validation error
     *
     * @covers \Milex\FormBundle\Validator\UploadFieldValidator::processFileValidation
     */
    public function testFileIsValid()
    {
        $fileUploadValidatorMock = $this->getMockBuilder(FileUploadValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileUploadValidatorMock->expects($this->once())
            ->method('validate');

        $parameterBagMock = $this->getMockBuilder(ParameterBag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileMock = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $files = [
            'file' => $fileMock,
        ];

        $parameterBagMock->expects($this->once())
            ->method('get')
            ->with('milexform')
            ->willReturn($files);

        $request        = new Request();
        $request->files = $parameterBagMock;

        $fileUploadValidator = new UploadFieldValidator($fileUploadValidatorMock);

        $field = new Field();
        $field->setAlias('file');
        $field->setProperties([
            'allowed_file_size'       => 1,
            'allowed_file_extensions' => ['jpg', 'gif'],
        ]);

        $file = $fileUploadValidator->processFileValidation($field, $request);

        $this->assertSame($fileMock, $file);
    }
}
