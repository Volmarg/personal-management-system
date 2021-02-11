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
    private $allowRefererForUrls = [];

    /**
     * @var Finder $finder
     */
    private $finder;

    /**
     * @var UrlGeneratorInterface $urlGenerator
     */
    private $urlGenerator;

    /**
     * @var Navigation $navigation
     */
    private $navigation;

    /**
     * @var LockedResourceController $lockedResourceController
     */
    private $lockedResourceController;

    public function __construct(UrlGeneratorInterface $urlGenerator, Navigation $navigation, LockedResourceController $lockedResourceController) {
        $this->finder       = new Finder();
        $this->urlGenerator = $urlGenerator;
        $this->navigation   = $navigation;

        $this->lockedResourceController = $lockedResourceController;

        $this->allowRefererForUrls = [
          $this->urlGenerator->generate('render_menu_node_template'),
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
     * @param $uploadModuleDir
     * @return array
     * @throws Exception
     */
    public function getUploadFolderSubdirectoriesTree($uploadModuleDir) {

        $targetDirectory = FileUploadController::getTargetDirectoryForUploadModuleDir($uploadModuleDir);
        $foldersTree     = DirectoriesHandler::buildFoldersTreeForDirectory( new DirectoryIterator( $targetDirectory) );

        return $foldersTree;

    }

    /**
     * @param string $uploadModuleDir
     * @return array
     * @throws Exception
     */
    public function getAllExistingUploadFolderSubdirectories(string $uploadModuleDir): array
    {
        $folderTree   = $this->getUploadFolderSubdirectoriesTree($uploadModuleDir);
        $foldersArray = UtilsController::arrayKeysMulti($folderTree);
        $folders      = [];

        foreach($foldersArray as  $folder){
            $folderShown           = FilesHandler::getSubdirectoryPathFromUploadModuleUploadFullPath($folder, $uploadModuleDir);
            $folders[$folderShown] = $folder;
        }

        return $folders;
    }

    /**
     * Not doing this in twig because of nested arrays functions limitation
     * @param string $uploadModuleDir
     * @return string
     * @throws Exception
     */
    public function buildMenuForUploadType(string $uploadModuleDir){

        $foldersTree = $this->getUploadFolderSubdirectoriesTree($uploadModuleDir);
        $list        = '';

        array_walk($foldersTree, function ($subfolderTree, $folderPath) use (&$list, $uploadModuleDir) {
           $list = $this->buildList($subfolderTree, $uploadModuleDir, $folderPath, $list);
        });


        return $list;
    }

    /**
     * @param array $folderTree
     * @param string $uploadModuleDir
     * @param string $list
     * @param string $folderPath
     * @return string
     * @throws Exception
     */
    private function buildList(array $folderTree, string $uploadModuleDir, string $folderPath, string $list = '') {

        $uploadFolder                = FileUploadController::getTargetDirectoryForUploadModuleDir($uploadModuleDir);
        $folderPathInModuleUploadDir = str_replace($uploadFolder . DIRECTORY_SEPARATOR, '', $folderPath);
        $moduleName                  = FileUploadController::MODULE_UPLOAD_DIR_TO_MODULE_NAME[$uploadModuleDir];
        $FolderName                  = basename($folderPath);

        //urlencoded is needed since entire path is single param in controller, but then we need to unescape escaped spacebars
        $encodedFolderPathInModuleUploadDir = urlencode($folderPathInModuleUploadDir);
        $folderPathWithUnescapedSpacebar    = str_replace("+"," ", $encodedFolderPathInModuleUploadDir);

        $href   = $this->buildPathForUploadModuleDir($folderPathWithUnescapedSpacebar, $uploadModuleDir);
        $link   = "<a class='sidebar-link' href='{$href}' style='display: inline;'>{$FolderName}</a>";

        $uri = $_SERVER['REQUEST_URI'];

        if( in_array($uri, $this->allowRefererForUrls) ){
            $uri = $_SERVER['HTTP_REFERER'];
        }

        $dropdownArrow = '';
        $class         = '';
        $isUrl         = false;
        $isOpen        = $this->navigation->keepMenuOpen($uri, '',  $href);

        if( !empty($folderTree) ){
            $dropdownArrow = static::DROPDOWN_ARROW_HTML;
            $class         = 'nav-item dropdown';
            $isUrl         = true;
        }

        //prevent rendering the given node if if any parent or the children itself is locked
        if( !$this->lockedResourceController->isAllowedToSeeResource($folderPath, LockedResource::TYPE_DIRECTORY, $moduleName, false) ){
            return $list;
        }


        $list  .= '<li class="' . $class . ' ' . $isOpen . ' folder-based-menu-element">'.$link.$dropdownArrow;

        if( $isUrl ) //prevent adding "open" class to menu elements which does not have any subtree
        {
            $list .= '<ul class="dropdown-menu folder-based-menu folder-based-menu-element" >';

            array_walk($folderTree, function ($subfolderTree, $folderPath) use (&$list, $uploadModuleDir) {
                $list = static::buildList($subfolderTree, $uploadModuleDir, $folderPath, $list);
            });

            $list .= '</ul>';
        }

        $list .= '</li>';

        return $list;
    }

    /**
     * @param string $uploadModuleDirectory
     * @param string $encodedSubdirectoryPath
     * @return string
     * @throws Exception
     */
    private function buildPathForUploadModuleDir(string $encodedSubdirectoryPath, string $uploadModuleDirectory) {

        switch($uploadModuleDirectory){
            case FileUploadController::MODULE_UPLOAD_DIR_FOR_FILES:
                $path = $this->urlGenerator->generate('modules_my_files', ['encodedSubdirectoryPath' => $encodedSubdirectoryPath]);
                break;
            case FileUploadController::MODULE_UPLOAD_DIR_FOR_IMAGES:
                $path = $this->urlGenerator->generate('modules_my_images', ['encodedSubdirectoryPath' => $encodedSubdirectoryPath]);
                break;
            case FileUploadController::MODULE_UPLOAD_DIR_FOR_VIDEO:
                $path = $this->urlGenerator->generate('modules_my_video', ['encodedSubdirectoryPath' => $encodedSubdirectoryPath]);
                break;
            default:
                throw new Exception("This upload directory is not supported: {$uploadModuleDirectory}");
        }

        return $path;

    }

}