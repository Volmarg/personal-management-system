<?php

namespace App\Services\Module\Storage;

use App\Response\Base\BaseResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class StorageFolderService
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ){}

    /**
     * @param string $existingDirPath
     * @param string $newDirName
     * @param string $newDirPath
     *
     * @return BaseResponse|null
     */
    public function validateCreateAndRename(string $existingDirPath, string $newDirName, string $newDirPath): ?BaseResponse
    {
        if (empty($newDirName)) {
            $msg = $this->translator->trans('module.storage.common.dirNameIsEmpty');
            return BaseResponse::buildBadRequestErrorResponse($msg);
        }

        if (empty($existingDirPath)) {
            $msg = $this->translator->trans('module.storage.common.existingDirPath');
            return BaseResponse::buildBadRequestErrorResponse($msg);
        }

        if (file_exists($newDirPath) && is_dir($newDirPath)) {
            $msg = $this->translator->trans('module.storage.common.dirExists');
            return BaseResponse::buildBadRequestErrorResponse($msg);
        }

        if (!is_writable($existingDirPath)) {
            $msg = $this->translator->trans('module.storage.common.dirNotWritable') . " {$existingDirPath}";
            return BaseResponse::buildBadRequestErrorResponse($msg);
        }

        return null;
    }

}