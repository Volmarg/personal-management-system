<?php

namespace App\Controller\Modules\Video;

use App\Controller\Core\Application;
use App\Controller\Core\Env;
use App\Entity\FilesTags;
use App\Services\Files\FileTagger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;

class MyVideoController extends AbstractController {

    const KEY_FILE_NAME           = 'file_name';
    const KEY_FILE_FULL_PATH      = 'file_full_path';
    const MODULE_NAME             = 'My Video';

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var Application $app
     */
    private $app;

    /**
     * Will return the target directory in context of upload dir being root
     * @return string
     */
    public static function getTargetUploadDir()
    {
        $dir = basename(Env::getVideoUploadDir());
        return $dir;
    }


    public function __construct(Application $app) {
        $this->finder = new Finder();
        $this->finder->depth('== 0');

        $this->app = $app;
    }

    /**
     * @param string $subdirectory
     * @return array
     */
    public function getVideosInCategory(string $subdirectory): array {
        $upload_dir       = Env::getVideoUploadDir();
        $all_images       = [];
        $search_dir       = ( empty($subdirectory) ? $upload_dir : $upload_dir . '/' . $subdirectory);

        $this->finder->files()->in($search_dir);

        foreach ($this->finder as $image) {

            $file_full_path = $image->getPath() . DIRECTORY_SEPARATOR . $image->getFilename();
            $file_tags      = $this->app->repositories->filesTagsRepository->getFileTagsEntityByFileFullPath($file_full_path);
            $tags_json      = ( $file_tags instanceof FilesTags ? $file_tags->getTags() : "" );

            $all_images[] = [
                static::KEY_FILE_FULL_PATH => $image->getPathname(),
                static::KEY_FILE_NAME      => $image->getFilename(),
                FileTagger::KEY_TAGS       => $tags_json
            ];
        }

        return $all_images;
    }

    /**
     * @return array
     */
    public function getMainFolderVideos(): array {
        $all_video_paths = $this->getVideosInCategory('');
        return $all_video_paths;
    }

}
