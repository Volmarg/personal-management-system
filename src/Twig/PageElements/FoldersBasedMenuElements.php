<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 16.05.19
 * Time: 20:34
 */

namespace App\Twig\PageElements;

use App\Controller\Files\FileUploadController;
use App\Controller\System\LockedResourceController;
use App\Controller\Utils\Utils as UtilsController;
use App\Entity\System\LockedResource;
use App\Services\Files\DirectoriesHandler;
use App\Services\Files\FilesHandler;
use App\Twig\Css\Navigation;
use DirectoryIterator;
use Exception;
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
     * This array contains url for which it's allowed to use HTTP_REFERER instead of REQUEST_URI to determine
     * which menu elements should be kept open, this is needed because of Quick Folder create widget which then reloads the menu node.
     * The request uri for that case is the ajax url uri, but the call for quick create comes from upload based module.
     * @var array
     */
    private $allow_referer_for_urls = [];

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var UrlGeneratorInterface $url_generator
     */
    private $url_generator;

    /**
     * @var Navigation $navigation
     */
    private $navigation;

    /**
     * @var LockedResourceController $lock_resource_controller
     */
    private $locked_resource_controller;

    public function __construct(UrlGeneratorInterface $url_generator, Navigation $navigation, LockedResourceController $locked_resource_controller) {
        $this->finder           = new Finder();
        $this->url_generator    = $url_generator;
        $this->navigation       = $navigation;

        $this->locked_resource_controller = $locked_resource_controller;

        $this->allow_referer_for_urls = [
          $this->url_generator->generate('render_menu_node_template'),
        ];
    }


    public function getFunctions() {
        return [
            new TwigFunction('getUploadFolderSubdirectoriesTree', [$this, 'getUploadFolderSubdirectoriesTree']),
            new TwigFunction('buildMenuForUploadType', [$this, 'buildMenuForUploadType']),
            new TwigFunction('getAllExistingUploadFolderSubdirectories', [$this, 'getAllExistingUploadFolderSubdirectories']),
        ];
    }


    /**
     * @param $upload_module_dir
     * @return array
     * @throws Exception
     */
    public function getUploadFolderSubdirectoriesTree($upload_module_dir) {

        $target_directory = FileUploadController::getTargetDirectoryForUploadModuleDir($upload_module_dir);
        $folders_tree     = DirectoriesHandler::buildFoldersTreeForDirectory( new DirectoryIterator( $target_directory) );

        return $folders_tree;

    }

    /**
     * @param string $upload_module_dir
     * @return array
     * @throws Exception
     */
    public function getAllExistingUploadFolderSubdirectories(string $upload_module_dir): array
    {
        $folder_tree   = $this->getUploadFolderSubdirectoriesTree($upload_module_dir);
        $folders_array = UtilsController::arrayKeysMulti($folder_tree);
        $folders       = [];

        foreach($folders_array as  $folder){
            $folder_shown           = FilesHandler::getSubdirectoryPathFromUploadModuleUploadFullPath($folder, $upload_module_dir);
            $folders[$folder_shown] = $folder;
        }

        return $folders;
    }

    /**
     * Not doing this in twig because of nested arrays functions limitation
     * @param string $upload_module_dir
     * @return string
     * @throws Exception
     */
    public function buildMenuForUploadType(string $upload_module_dir){

        $folders_tree   = $this->getUploadFolderSubdirectoriesTree($upload_module_dir);
        $script         = $this->keepUploadBasedMenuOpenJS();
        $list           = '';

        array_walk($folders_tree, function ($subfolder_tree, $folder_path) use (&$list, $upload_module_dir) {
           $list = $this->buildList($subfolder_tree, $upload_module_dir, $folder_path, $list);
        });


        return $list.$script;
    }

    /**
     * @param array $folder_tree
     * @param string $upload_module_dir
     * @param string $list
     * @param string $folder_path
     * @return string
     * @throws Exception
     */
    private function buildList(array $folder_tree, string $upload_module_dir, string $folder_path, string $list = '') {

        $upload_folder                      = FileUploadController::getTargetDirectoryForUploadModuleDir($upload_module_dir);
        $folder_path_in_module_upload_dir   = str_replace($upload_folder . DIRECTORY_SEPARATOR, '', $folder_path);
        $module_name                        = FileUploadController::MODULE_UPLOAD_DIR_TO_MODULE_NAME[$upload_module_dir];
        $folder_name                        = basename($folder_path);

        //urlencoded is needed since entire path is single param in controller, but then we need to unescape escaped spacebars
        $encoded_folder_path_in_module_upload_dir = urlencode($folder_path_in_module_upload_dir);
        $folder_path_with_unescaped_spacebar      = str_replace("+"," ", $encoded_folder_path_in_module_upload_dir);

        $href   = $this->buildPathForUploadModuleDir($folder_path_with_unescaped_spacebar, $upload_module_dir);
        $link   = "<a class='sidebar-link' href='{$href}' style='display: inline;'>{$folder_name}</a>";

        $uri = $_SERVER['REQUEST_URI'];

        if( in_array($uri, $this->allow_referer_for_urls) ){
            $uri = $_SERVER['HTTP_REFERER'];
        }

        $dropdown_arrow = '';
        $class          = '';
        $is_url         = false;
        $isOpen         = $this->navigation->keepMenuOpen($uri, '',  $href);

        if( !empty($folder_tree) ){
            $dropdown_arrow = static::DROPDOWN_ARROW_HTML;
            $class         = 'nav-item dropdown';
            $is_url        = true;
        }

        //prevent rendering the given node if if any parent or the children itself is locked
        if( !$this->locked_resource_controller->isAllowedToSeeResource($folder_path, LockedResource::TYPE_DIRECTORY, $module_name, false) ){
            return $list;
        }


        $list  .= '<li class="' . $class . ' ' . $isOpen . ' folder-based-menu-element">'.$link.$dropdown_arrow;

        if( $is_url ) //prevent adding "open" class to menu elements which does not have any subtree
        {
            $list .= '<ul class="dropdown-menu folder-based-menu folder-based-menu-element" >';

            array_walk($folder_tree, function ($subfolder_tree, $folder_path) use (&$list, $upload_module_dir) {
                $list = static::buildList($subfolder_tree, $upload_module_dir, $folder_path, $list);
            });

            $list .= '</ul>';
        }

        $list .= '</li>';

        return $list;
    }

    /**
     * @param string $upload_module_directory
     * @param string $encoded_subdirectory_path
     * @return string
     * @throws Exception
     */
    private function buildPathForUploadModuleDir(string $encoded_subdirectory_path, string $upload_module_directory) {

        switch($upload_module_directory){
            case FileUploadController::MODULE_UPLOAD_DIR_FOR_FILES:
                $path = $this->url_generator->generate('modules_my_files', ['encoded_subdirectory_path' => $encoded_subdirectory_path]);
                break;
            case FileUploadController::MODULE_UPLOAD_DIR_FOR_IMAGES:
                $path = $this->url_generator->generate('modules_my_images', ['encoded_subdirectory_path' => $encoded_subdirectory_path]);
                break;
            default:
                throw new Exception("This upload directory is not supported: {$upload_module_directory}");
        }

        return $path;

    }

    /**
     * This code will be evaluated once menu is built
     * In normal cases this is handled in twig via keepMenuOpen()
     */
    private function keepUploadBasedMenuOpenJS() {

        $script= "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    utils.ui.keepUploadBasedMenuOpen();
                });
            </script>
        ";

        return $script;
    }

}