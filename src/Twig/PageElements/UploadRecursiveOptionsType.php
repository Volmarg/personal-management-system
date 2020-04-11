<?php

namespace App\Twig\PageElements;

use App\Controller\Files\FileUploadController;
use App\Services\Files\DirectoriesHandler;
use DirectoryIterator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * This class is specifically used for working with folders structure for form type: "UploadrecursiveoptionsType"
 * Class FoldersBasedMenuElements
 * @package App\Twig\PageElements
 */
class UploadRecursiveOptionsType extends AbstractExtension {

    const INDENTS_FIXED_VALUE = 1;

    /**
     * @var UrlGeneratorInterface $url_generator
     */
    private $url_generator;

    public function __construct(UrlGeneratorInterface $url_generator) {
        $this->url_generator    = $url_generator;
    }

    public function getFunctions() {
        return [
            new TwigFunction('buildOptionsForUploadFormType', [$this, 'buildOptionsForUploadFormType']),
        ];
    }

    /**
     * Not doing this in twig because of nested arrays functions limitation
     * @param string $upload_module_dir
     * @param bool $add_main_folder
     * @return string
     * @throws \Exception
     */
    public function buildOptionsForUploadFormType(string $upload_module_dir, bool $add_main_folder = false){
        $target_directory = FileUploadController::getTargetDirectoryForUploadModuleDir($upload_module_dir);
        $folders_tree     = DirectoriesHandler::buildFoldersTreeForDirectory( new DirectoryIterator( $target_directory ));
        $options          = "";

        if($add_main_folder){
            $array[$target_directory] = [];
            $folders_tree             = array_merge($array, $folders_tree);
        }

        $options .= "<optgroup label='{$upload_module_dir}'>";

        array_walk($folders_tree, function ($subarray, $folder_path) use (&$options, $upload_module_dir) {
            $options = $this->buildList($subarray, $upload_module_dir, $folder_path, $options);
        });

        $options .= '</optgroup>';

        return $options;
    }

    /**
     * @param array $folders_tree
     * @param string $upload_module_dir
     * @param string $options
     * @param string $folder_path
     * @return string
     * @throws \Exception
     */
    private function buildList(array $folders_tree, string $upload_module_dir, string $folder_path, string $options = '') {

        $target_directory                = FileUploadController::getTargetDirectoryForUploadModuleDir($upload_module_dir);
        $folder_path_in_target_directory = str_replace($target_directory.'/', '', $folder_path);

        $folder_name      = ( $folder_path === $target_directory ? FileUploadController::KEY_MAIN_FOLDER : basename($folder_path) );
        $folder_depth     = substr_count($folder_path , "/");
        $separator        = '';

        if(
                ( $folder_depth > static::INDENTS_FIXED_VALUE )
            &&  ( $folder_name !== FileUploadController::KEY_MAIN_FOLDER )
        ){

            for($x = static::INDENTS_FIXED_VALUE; $x < $folder_depth; $x++){
                $separator .= '&nbsp;&nbsp;';
            }

        }

        $options  .= "<option value='{$folder_path_in_target_directory}'>".$separator.$folder_name;

        array_walk($folders_tree, function ($subarray, $folder_path) use (&$options, $upload_module_dir) {
            $options = static::buildList($subarray, $upload_module_dir, $folder_path, $options);
        });

        $options .= "</option>";

        return $options;
    }

}