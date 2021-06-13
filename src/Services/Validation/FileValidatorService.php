<?php

namespace App\Services\Validation;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Validator\Validation;

/**
 * Handles files validations logic
 *
 * Class FileValidatorService
 * @package App\Services\Validation
 */
class FileValidatorService {

    // Images mime types
    const MIME_TYPE_APNG    = 'image/apng';
    const MIME_TYPE_BMP     = 'image/bmp';
    const MIME_TYPE_GIF     = 'image/gif';
    const MIME_TYPE_X_ICON  = 'image/x-icon';
    const MIME_TYPE_JPEG    = 'image/jpeg';
    const MIME_TYPE_PNG     = 'image/png';
    const MIME_TYPE_SVG     = 'image/svg+xml';
    const MIME_TYPE_TIFF    = 'image/tiff';
    const MIME_TYPE_WEBP    = 'image/webp';

    const IMAGES_MIME_TYPES = [
      self::MIME_TYPE_APNG,
      self::MIME_TYPE_BMP,
      self::MIME_TYPE_GIF,
      self::MIME_TYPE_X_ICON,
      self::MIME_TYPE_JPEG,
      self::MIME_TYPE_PNG,
      self::MIME_TYPE_SVG,
      self::MIME_TYPE_TIFF,
      self::MIME_TYPE_WEBP,
    ];

    const RESIZABLE_MIME_TYPES = [
        self::MIME_TYPE_JPEG,
        self::MIME_TYPE_PNG,
        self::MIME_TYPE_APNG,
        self::MIME_TYPE_BMP,
    ];

    /**
     * @var ConstraintValidationService $validationService
     */
    private ConstraintValidationService $validationService;

    public function __construct(ConstraintValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Will check if given file is an image by checking it's mime type
     *
     * @param File $file
     * @return bool
     */
    public function isFileImage(File $file): bool
    {
        $validator      = Validation::createValidator();
        $violationsList = $validator->validate($file, new FileConstraint([
            'mimeTypes' => self::IMAGES_MIME_TYPES,
        ]));

        $validationResult = $this->validationService->checkConstraintViolationsAndReturnValidationResultVo($violationsList);
        return $validationResult->isValid();
    }

    /**
     * Will check if given image can be resized by checking it's mime type
     * for example gif cannot be resized as it can be animated, or transparency can be broken.
     *
     * @param File $file
     * @return bool
     */
    public function isImageResizable(File $file): bool
    {
        $validator      = Validation::createValidator();
        $violationsList = $validator->validate($file, new FileConstraint([
            'mimeTypes' => self::RESIZABLE_MIME_TYPES,
        ]));

        $validationResult = $this->validationService->checkConstraintViolationsAndReturnValidationResultVo($violationsList);
        return $validationResult->isValid();
    }

}