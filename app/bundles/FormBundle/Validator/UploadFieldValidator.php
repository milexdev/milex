<?php

namespace Milex\FormBundle\Validator;

use Milex\CoreBundle\Exception\FileInvalidException;
use Milex\CoreBundle\Validator\FileUploadValidator;
use Milex\FormBundle\Entity\Field;
use Milex\FormBundle\Exception\FileValidationException;
use Milex\FormBundle\Exception\NoFileGivenException;
use Milex\FormBundle\Form\Type\FormFieldFileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class UploadFieldValidator
{
    /**
     * @var FileUploadValidator
     */
    private $fileUploadValidator;

    public function __construct(FileUploadValidator $fileUploadValidator)
    {
        $this->fileUploadValidator = $fileUploadValidator;
    }

    /**
     * @return UploadedFile
     *
     * @throws FileValidationException
     * @throws NoFileGivenException
     */
    public function processFileValidation(Field $field, Request $request)
    {
        $files = $request->files->get('milexform');

        if (!$files || !array_key_exists($field->getAlias(), $files)) {
            throw new NoFileGivenException();
        }

        /** @var UploadedFile $file */
        $file = $files[$field->getAlias()];

        if (!$file instanceof UploadedFile) {
            throw new NoFileGivenException();
        }

        $properties = $field->getProperties();

        $maxUploadSize     = $properties[FormFieldFileType::PROPERTY_ALLOWED_FILE_SIZE];
        $allowedExtensions = $properties[FormFieldFileType::PROPERTY_ALLOWED_FILE_EXTENSIONS];

        try {
            $this->fileUploadValidator->validate($file->getSize(), $file->getClientOriginalExtension(), $maxUploadSize, $allowedExtensions, 'milex.form.submission.error.file.extension', 'milex.form.submission.error.file.size');

            return $file;
        } catch (FileInvalidException $e) {
            throw new FileValidationException($e->getMessage());
        }
    }
}
