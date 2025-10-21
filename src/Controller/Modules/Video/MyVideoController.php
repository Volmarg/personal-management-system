<?php

namespace App\Controller\Modules\Video;

use App\Controller\Core\Env;
use App\Entity\FilesTags;
use App\Repository\FilesTagsRepository;
use App\Services\Files\FileTagger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;

class MyVideoController extends AbstractController {

    /**
     * @var Finder $finder
     */
    private $finder;

    public function __construct(
        private readonly FilesTagsRepository $filesTagsRepository,
    ) {
        $this->finder = new Finder();
        $this->finder->depth('== 0');
    }

    /**
     * @param string $subdirectory
     * @return array
     */
    public function getVideosInCategory(string $subdirectory): array {
        $uploadDir = Env::getVideoUploadDir();
        $allImages = [];
        $searchDir = ( empty($subdirectory) ? $uploadDir : $uploadDir . '/' . $subdirectory);

        $this->finder->files()->in($searchDir);

        foreach ($this->finder as $image) {

            $fileFullPath = $image->getPath() . DIRECTORY_SEPARATOR . $image->getFilename();
            $fileTags     = $this->filesTagsRepository->getFileTagsEntityByFileFullPath($fileFullPath);
            $tagsJson     = ( $fileTags instanceof FilesTags ? $fileTags->getTags() : "" );

            $allImages[] = [
                'file_full_path' => $image->getPathname(),
                'file_name'      => $image->getFilename(),
                FileTagger::KEY_TAGS       => $tagsJson
            ];
        }

        return $allImages;
    }

}
