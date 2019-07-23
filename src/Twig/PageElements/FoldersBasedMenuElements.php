<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 16.05.19
 * Time: 20:34
 */

namespace App\Twig\PageElements;

use App\Controller\FileUploadController;
use Symfony\Component\Finder\Finder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FoldersBasedMenuElements extends AbstractExtension {

    /**
     * @var Finder $finder
     */
    private $finder;

    public function __construct() {
        $this->finder       = new Finder();
    }


    public function getFunctions() {
        return [
            new TwigFunction('getUploadFolderSubdirectories', [$this, 'getUploadFolderSubdirectories']),
        ];
    }

    /**
     * @param $uploadType
     * @return array
     * @throws \Exception
     */
    public function getUploadFolderSubdirectories($uploadType) {

        $subdirectories = FileUploadController::getSubdirectoriesForUploadType($uploadType);
        return $subdirectories;

    }

}