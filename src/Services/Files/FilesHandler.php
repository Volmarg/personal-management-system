<?php

namespace App\Services\Files;

/**
 * This service is responsible for handling files in terms of internal usage, like moving/renaming/etc...
 * Class FilesHandler
 * @package App\Services
 */
class FilesHandler
{

    /**
     * Removes first and last slash from $dirPath
     *
     * @param string $dirPath
     *
     * @return bool|string
     */
    public static function trimFirstAndLastSlash(string $dirPath)
    {
        $trimmedDirPath = $dirPath;

        $isLeadingSlash = (substr($dirPath, 0, 1) === DIRECTORY_SEPARATOR);
        $isLastSlash    = (substr($dirPath, -1) === DIRECTORY_SEPARATOR);

        if ($isLeadingSlash) {
            $trimmedDirPath = substr($trimmedDirPath, 1);
        }

        if ($isLastSlash) {
            $trimmedDirPath = substr($trimmedDirPath, 0, -1);
        }

        return $trimmedDirPath;
    }

    /**
     * This function will return file path with leading slash if such is missing
     *
     * @param string $filePath
     * @param bool   $skipAddingForLinks
     *
     * @return string
     */
    public static function addTrailingSlashIfMissing(string $filePath, $skipAddingForLinks = false): string
    {

        $isFilePathWithoutTrailingSlash = (0 !== strpos($filePath, DIRECTORY_SEPARATOR));

        $isSkipped          = false;
        $matchesToSkipLinks = [
            "www",
            "http",
        ];

        if ($isFilePathWithoutTrailingSlash) {

            foreach ($matchesToSkipLinks as $singleMatch) {
                if (strstr($filePath, $singleMatch)) {
                    $isSkipped = true;
                    break;
                }
            }

            if (!$isSkipped) {
                $filePath = DIRECTORY_SEPARATOR . $filePath;
            }
        }

        return $filePath;
    }

}