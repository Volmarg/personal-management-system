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

/**
 * This class is specifically used for working with folders structure for upload modules!
 * Class FoldersBasedMenuElements
 * @package App\Twig\PageElements
 */
class FoldersBasedMenuElements extends AbstractExtension {

    const DROPDOWN_ARROW_HTML = '<a class="sidebar-link" href="javascript:void(0);" style="display:inline;">
                                    <span class="arrow"><i class="ti-angle-right"></i></span>
                                 </a>';

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
     * @param $upload_type
     * @return array
     * @throws \Exception
     */
    public function getUploadFolderSubdirectories($upload_type) {

        $subdirectories = FileUploadController::getSubdirectoriesForUploadType($upload_type);
        return $subdirectories;

    }

    /**
     * @param $upload_type
     * @return array
     * @throws \Exception
     */
    public function getUploadFolderSubdirectories_new($upload_type) {

        $target_directory = FileUploadController::getTargetDirectoryForUploadType($upload_type);
        $folders_tree     = DirectoriesHandler::buildFoldersTreeForDirectory( new DirectoryIterator( $target_directory) );

        return $folders_tree;

    }

    /**
     * Not doing this in twig because of nested arrays functions limitation
     * TODO: rename func?
     * @param string $upload_type
     * @return string
     * @throws \Exception
     */
    public function buildMenuForUploadType(string $upload_type){

        $folders_tree   = $this->getUploadFolderSubdirectories_new($upload_type);
        $list           = '';

        array_walk($folders_tree, function ($subarray, $folder_path) use (&$list, $upload_type) {
           $list = $this->buildList($subarray, $upload_type, $folder_path, $list);
        });

        return $list;
    }

    /**
     * @param array $folders_tree
     * @param string $upload_type
     * @param string $list
     * @param string $folder_path
     * @return string
     * @throws \Exception
     */
    private function buildList(array $folders_tree, string $upload_type, string $folder_path, string $list = '') {

        $upload_folder              = FileUploadController::getTargetDirectoryForUploadType($upload_type);
        $folder_path_in_upload_type = str_replace($upload_folder, '', $folder_path);
        $folder_name                = basename($folder_path);

        $encoded_folder_path_in_upload_type = urlencode($folder_path_in_upload_type);

        $href   = $this->buildPathForUploadType($encoded_folder_path_in_upload_type, $upload_type);
        $link   = "<a class='sidebar-link' href='{$href}' style='display: inline;'>{$folder_name}</a>";

        $list  .= '<li class="nav-item dropdown">';
        $list  .= $link;

        if( empty(!$folders_tree) ){
            $list .= static::DROPDOWN_ARROW_HTML;
        }

        $list .= '<ul class="dropdown-menu" >';

        array_walk($folders_tree, function ($subarray, $folder_path) use (&$list, $upload_type) {
            $list = static::buildList($subarray, $upload_type, $folder_path, $list);
        });

        $list .= '</ul>';
        $list .= '</li>';

        return $list;
    }

    /**
     * @param string $upload_type
     * @param string $subdirectory
     * @return string
     * @throws \Exception
     */
    private function buildPathForUploadType(string $subdirectory, string $upload_type) {

        switch($upload_type){
            case FileUploadController::TYPE_FILES:
                $path = $this->url_generator->generate('modules_my_files', ['subdirectory' => $subdirectory]);
                break;

            default:
                throw new \Exception("This upload type is not supported: {$upload_type}");
        }

        return $path;

    }

}