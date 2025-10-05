<?php

namespace App\Services\Files;

use Symfony\Component\Finder\Finder;

/**
 * This service is responsible for handling files in terms of internal usage, like moving/renaming/etc...
 * Class FilesHandler
 * @package App\Services
 */
class FilesHandler
{

    /**
     * Will list all files in given directories
     *
     * @param array $directories
     *
     * @return array
     */
    public function listAllFilesInDirectories(array $directories): array
    {
        $filesPathsList = [];

        foreach ($directories as $directory) {
            $finder = new Finder();
            $finder->depth(0);
            $finder->files()->in($directory);

            foreach ($finder as $file) {
                $filesPathsList[$directory][] = $file->getFilename();
            }
        }

        return $filesPathsList;
    }

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
     * @param string $dirPath
     *
     * @return int
     */
    public static function countFilesInTree(string $dirPath)
    {
        $finder = new Finder();
        $finder->files()->in($dirPath);
        $filesCountInTree = count($finder);

        return $filesCountInTree;
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