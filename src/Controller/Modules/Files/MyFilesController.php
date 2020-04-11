<?php

namespace App\Controller\Modules\Files;

use App\Controller\Files\FileUploadController;
use App\Controller\Core\Application;
use App\Controller\Core\Env;
use App\Entity\FilesTags;
use App\Services\Files\FileTagger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

class MyFilesController extends AbstractController
{

    const KEY_FILE_NAME      = 'file_name';
    const KEY_FILE_SIZE      = 'file_size';
    const KEY_FILE_EXTENSION = 'file_extension';
    const KEY_FILE_FULL_PATH = 'file_full_path';
    const KEY_SUBDIRECTORY   = 'subdirectory';
    const MODULE_NAME        = 'My Files';
    const TARGET_UPLOAD_DIR  = 'files';

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
     */
    public function getFilesFromSubdirectory(string $subdirectory):? array
    {
        $upload_dir = Env::getFilesUploadDir();
        $all_files  = [];
        $search_dir = ( empty($subdirectory) ? $upload_dir : FileUploadController::getSubdirectoryPath($upload_dir, $subdirectory));

        try{
            $this->finder->files()->in($search_dir);
        }catch(DirectoryNotFoundException $de){
            return null;
        }

        foreach ($this->finder as $index => $file) {

            $file_full_path = $file->getPath() . '/' . $file->getFilename();
            $file_tags      = $this->app->repositories->filesTagsRepository->getFileTagsEntityByFileFullPath($file_full_path);
            $tags_json      = ( $file_tags instanceof FilesTags ? $file_tags->getTags() : "" );

            $all_files[$index] = [
                static::KEY_FILE_NAME      => $file->getFilenameWithoutExtension(),
                static::KEY_FILE_SIZE      => $file->getSize(),
                static::KEY_FILE_EXTENSION => $file->getExtension(),
                static::KEY_FILE_FULL_PATH => $file_full_path,
                FileTagger::KEY_TAGS       => $tags_json
            ];

        }

        return $all_files;
    }

    /**
     * @return array|null
     */
    public function getMainFolderFiles() {
        $all_files = $this->getFilesFromSubdirectory('');

        return $all_files;
    }

}
