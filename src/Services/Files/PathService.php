<?php

namespace App\Services\Files;

use App\Controller\Core\Env;
use App\Enum\File\UploadedFileSourceEnum;
use App\Enum\StorageModuleEnum;
use LogicException;

/**
 * Handles directories related logic
 */
class PathService
{
    /**
     * Checks if given path ends with dir separator and if it does then nothing happen, else adds the separator,
     * This function does not care if provided path is directory or not.
     *
     * @param string $path
     *
     * @return string
     */
    public static function setTrailingDirSeparator(string $path): string
    {
        return (str_ends_with($path, DIRECTORY_SEPARATOR) ? $path : $path . DIRECTORY_SEPARATOR);
    }

    /**
     * @return string
     */
    public static function getVideoModuleUploadDir(): string
    {
        return self::setTrailingDirSeparator(Env::getPublicRootDir()) . Env::getVideoUploadDir();
    }

    /**
     * @return string
     */
    public static function getImageModuleUploadDir(): string
    {
        return self::setTrailingDirSeparator(Env::getPublicRootDir()) . Env::getImagesUploadDir();
    }

    /**
     * @return string
     */
    public static function getFileModuleUploadDir(): string
    {
        return self::setTrailingDirSeparator(Env::getPublicRootDir()) . Env::getFilesUploadDir();
    }

    /**
     * @return string
     */
    public static function getProfileImageUploadDir(): string
    {
        return self::setTrailingDirSeparator(Env::getUploadDir()) . UploadedFileSourceEnum::PROFILE_IMAGE->value;
    }

    /**
     * Returns the path used for accessing file from font
     *
     * @param string $filePath
     * @param bool   $isUpload
     *
     * @return string
     */
    public static function getPublicPath(string $filePath, bool $isUpload = true): string
    {
        self::validatePathSafety($filePath);
        if ($isUpload) {
            preg_match("#" . self::setTrailingDirSeparator(Env::getUploadDir()) . "(.*)#", $filePath, $matches);
            $matchingPath = $matches[1] ?? null;
            if (empty($matchingPath)) {
                throw new LogicException("This file cannot be used for public access. Got file path: {$filePath}");
            }

            return self::setTrailingDirSeparator(Env::getUploadDir()) . $matchingPath;
        }

        return $filePath;
    }

    /**
     * Checks if the path is safe to be used / accessed,
     * for example, don't want someone to call "../../../../../etc" for whatever reason
     *
     * @param string $filePath
     */
    public static function validatePathSafety(string $filePath): void
    {
        if (str_contains($filePath, "..") || str_contains($filePath, "../")) {
            throw new LogicException("Unsafe file path detected, got path: {$filePath}");
        }
    }

    /**
     * Returns base upload dir for storage module
     *
     * @param StorageModuleEnum $module
     *
     * @return string
     */
    public static function getStorageModuleBaseDir(StorageModuleEnum $module): string
    {
        return match ($module->value) {
            StorageModuleEnum::FILES->value => self::getFileModuleUploadDir(),
            StorageModuleEnum::IMAGES->value => self::getImageModuleUploadDir(),
            StorageModuleEnum::VIDEOS->value => self::getVideoModuleUploadDir(),
            default => throw new LogicException("Unsupported storage module: {$module->value}"),
        };
    }

    /**
     * @return array
     */
    public static function getAllStorageBaseDirs(): array
    {
        return [
            Env::getFilesUploadDir(),
            Env::getVideoUploadDir(),
            Env::getImagesUploadDir(),
        ];
    }
}