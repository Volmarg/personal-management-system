<?php

namespace App\Twig\PageElements;

use App\Controller\Files\FileUploadController;
use App\Services\DirectoriesHandler;
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

    const INDENTS_FIXED_VALUE = 2;

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
     * @param string $upload_type
     * @return string
     * @throws \Exception
     */
    public function buildOptionsForUploadFormType(string $upload_type){
        $target_directory = FileUploadController::getTargetDirectoryForUploadType($upload_type);
        $folders_tree     = DirectoriesHandler::buildFoldersTreeForDirectory( new DirectoryIterator( $target_directory) );
        $options          = "";

        $options .= "<optgroup label='{$upload_type}'>";

        array_walk($folders_tree, function ($subarray, $folder_path) use (&$options, $upload_type) {
            $options = $this->buildList($subarray, $upload_type, $folder_path, $options);
        });

        $options .= '</optgroup>';

        return $options;
    }

    /**
     * @param array $folders_tree
     * @param string $upload_type
     * @param string $options
     * @param string $folder_path
     * @return string
     * @throws \Exception
     */
    private function buildList(array $folders_tree, string $upload_type, string $folder_path, string $options = '') {
        $folder_name    = basename($folder_path);
        $folder_depth   = substr_count($folder_path , "/");
        $separator      = '';

        if( $folder_depth > static::INDENTS_FIXED_VALUE){

            for($x = static::INDENTS_FIXED_VALUE; $x < $folder_depth; $x++){
                $separator .= '&nbsp;';
            }

        }

        $options  .= "<option value='{$folder_name}'>".$separator.$folder_name;

        array_walk($folders_tree, function ($subarray, $folder_path) use (&$options, $upload_type) {
            $options = static::buildList($subarray, $upload_type, $folder_path, $options);
        });

        $options .= "</option>";

        return $options;
    }

}