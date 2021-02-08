<?php

namespace App\Controller\Modules\Files;

use App\Controller\Files\FileUploadController;
use App\Controller\Core\Application;
use App\Controller\Core\Env;
use App\Entity\FilesTags;
use App\Services\Files\FileTagger;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

class MyFilesController extends AbstractController
{

    const KEY_FILE_NAME          = 'file_name';
    const KEY_FILE_SIZE          = 'file_size';
    const KEY_FILE_EXTENSION     = 'file_extension';
    const KEY_FILE_FULL_PATH     = 'file_full_path';
    const KEY_FILE_MODIFIED_DATE = 'modified_date';
    const KEY_SUBDIRECTORY       = 'subdirectory';
    const MODULE_NAME            = 'My Files';
    const TARGET_UPLOAD_DIR      = 'files';

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->finder = new Finder();
        $this->finder->depth('== 0');

        $this->app = $app;
    }

    /**
     * @param string $subdirectory
     * @return array|null
     * @throws \Exception
     */
    public function getFilesFromSubdirectory(string $subdirectory):? array
    {
        $uploadDir = Env::getFilesUploadDir();
        $allFiles  = [];
        $searchDir = ( empty($subdirectory) ? $uploadDir : FileUploadController::getSubdirectoryPath($uploadDir, $subdirectory));

        try{
            $this->finder->files()->in($searchDir);
        }catch(DirectoryNotFoundException $de){
            return null;
        }

        foreach ($this->finder as $index => $file) {

            $fileFullPath = $file->getPath() . '/' . $file->getFilename();
            $fileTags     = $this->app->repositories->filesTagsRepository->getFileTagsEntityByFileFullPath($fileFullPath);
            $tagsJson     = ( $fileTags instanceof FilesTags ? $fileTags->getTags() : "" );

            $allFiles[$index] = [
                static::KEY_FILE_NAME           => $file->getFilenameWithoutExtension(),
                static::KEY_FILE_SIZE           => $file->getSize(),
                static::KEY_FILE_EXTENSION      => $file->getExtension(),
                static::KEY_FILE_FULL_PATH      => $fileFullPath,
                static::KEY_FILE_MODIFIED_DATE  => (new DateTime())->setTimestamp($file->getMTime())->format("Y-m-d H:i:s"),
                FileTagger::KEY_TAGS            => $tagsJson,
            ];

        }

        return $allFiles;
    }

    /**
     * @return array|null
     */
    public function getMainFolderFiles() {
        $allFiles = $this->getFilesFromSubdirectory('');

        return $allFiles;
    }

}
