<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 16.05.19
 * Time: 20:34
 */

namespace App\Twig\PageElements;

use App\Controller\Files\FileUploadController;
use App\Services\DirectoriesHandler;
use DirectoryIterator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FoldersBasedMenuElements extends AbstractExtension {

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var UrlGeneratorInterface $url_generator
     */
    private $url_generator;

    public function __construct(UrlGeneratorInterface $url_generator) {
        $this->finder           = new Finder();
        $this->url_generator    = $url_generator;
    }


    public function getFunctions() {
        return [
            new TwigFunction('getUploadFolderSubdirectories', [$this, 'getUploadFolderSubdirectories']),
            new TwigFunction('getUploadFolderSubdirectories_new', [$this, 'getUploadFolderSubdirectories_new']),
            new TwigFunction('buildMenuForUploadType', [$this, 'buildMenuForUploadType']),
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

    /**
     * @param $uploadType
     * @return array
     * @throws \Exception
     */
    public function getUploadFolderSubdirectories_new($uploadType) {

        $target_directory = FileUploadController::getTargetDirectoryForUploadType($uploadType);
        $folders_tree     = DirectoriesHandler::buildFoldersTreeForDirectory( new DirectoryIterator( $target_directory) );

        return $folders_tree;

    }

    /**
     * Not doing this in twig because of nested arrays functions limitation
     * TODO: rename func?
     * @param string $uploadType
     * @return string
     * @throws \Exception
     */
    public function buildMenuForUploadType(string $uploadType){

        $folders_tree   = $this->getUploadFolderSubdirectories_new($uploadType);
        $list           = '';

        array_walk($folders_tree, function ($subarray, $key) use (&$list, $uploadType) {

           $list = $this->buildList($subarray, $uploadType, $list, $key);
        });


        return $list;
    }

    /**
     * @param array $folders_tree
     * @param string $uploadType
     * @param string $list
     * @param string $key
     * @return string
     */
    private function buildList(array $folders_tree, string $uploadType, string $list = '', $key = ''){

        if( !empty($key) ){
            $href = $this->buildPathForUploadType($key, $uploadType);
            $link   = "<a class='sidebar-link' href='{$href}' style='display: inline;'>{$key}</a>";
        }

        $list .= '<li class="nav-item dropdown">';

        if( isset($link) ){
            $arrow = '<a class="sidebar-link" href="javascript:void(0);" style="display:inline;">
                            <span class="arrow"><i class="ti-angle-right"></i></span>
                        </a>';
            $list .= $link.$arrow;
        }

        $list .= '<ul class="dropdown-menu" >';

        array_walk($folders_tree, function ($subarray, $folder_name) use (&$list, $uploadType) {

            $list .= '<li>';
            // BUG: paths are generated incorrectly at this point
                $href = $this->buildPathForUploadType($folder_name, $uploadType);

                $link = "<a class='sidebar-link' href='{$href}' style='display: inline;'>{$folder_name}</a>";

                $list .= $link;

            $list .= '</li>';

            $list = static::buildList($subarray, $uploadType, $list);
        });

        $list .= '</ul>';
        $list .= '</li>';

        return $list;
    }

    /**
     * @param string $uploadType
     * @param string $subdirectory
     * @return string
     * @throws \Exception
     */
    private function buildPathForUploadType(string $subdirectory, string $uploadType) {

        switch($uploadType){
            case FileUploadController::TYPE_FILES:
                $path = $this->url_generator->generate('modules_my_files', ['subdirectory' => $subdirectory]);
                break;

            default:
                throw new \Exception("This upload type is not supported: {$uploadType}");
        }

        return $path;

    }

}