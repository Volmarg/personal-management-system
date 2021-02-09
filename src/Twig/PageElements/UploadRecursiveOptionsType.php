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
     * @var UrlGeneratorInterface $urlGenerator
     */
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $url_generator) {
        $this->urlGenerator = $url_generator;
    }

    public function getFunctions() {
        return [
            new TwigFunction('buildOptionsForUploadFormType', [$this, 'buildOptionsForUploadFormType']),
        ];
    }

    /**
     * Not doing this in twig because of nested arrays functions limitation
     * @param string $uploadModuleDir
     * @param bool $addMainFolder
     * @return string
     * @throws \Exception
     */
    public function buildOptionsForUploadFormType(string $uploadModuleDir, bool $addMainFolder = false){
        $targetDirectory = FileUploadController::getTargetDirectoryForUploadModuleDir($uploadModuleDir);
        $foldersTree     = DirectoriesHandler::buildFoldersTreeForDirectory( new DirectoryIterator( $targetDirectory ));
        $options         = "";

        if($addMainFolder){
            $array[$targetDirectory] = [];
            $foldersTree             = array_merge($array, $foldersTree);
        }

        $options .= "<optgroup label='{$uploadModuleDir}'>";

        array_walk($foldersTree, function ($subarray, $folderPath) use (&$options, $uploadModuleDir) {
            $options = $this->buildList($subarray, $uploadModuleDir, $folderPath, $options);
        });

        $options .= '</optgroup>';

        return $options;
    }

    /**
     * @param array $foldersTree
     * @param string $uploadModuleDir
     * @param string $options
     * @param string $folderPath
     * @return string
     * @throws \Exception
     */
    private function buildList(array $foldersTree, string $uploadModuleDir, string $folderPath, string $options = '') {

        $targetDirectory             = FileUploadController::getTargetDirectoryForUploadModuleDir($uploadModuleDir);
        $folderPathInTargetDirectory = str_replace($targetDirectory.'/', '', $folderPath);

        $folderName  = ( $folderPath === $targetDirectory ? FileUploadController::KEY_MAIN_FOLDER : basename($folderPath) );
        $folderDepth = substr_count($folderPath , "/");
        $separator   = '';

        if(
                ( $folderDepth > static::INDENTS_FIXED_VALUE )
            &&  ( $folderName !== FileUploadController::KEY_MAIN_FOLDER )
        ){

            for($x = static::INDENTS_FIXED_VALUE; $x < $folderDepth; $x++){
                $separator .= '&nbsp;&nbsp;';
            }

        }

        $options  .= "<option value='{$folderPathInTargetDirectory}'>".$separator.$folderName;

        array_walk($foldersTree, function ($subarray, $folderPath) use (&$options, $uploadModuleDir) {
            $options = static::buildList($subarray, $uploadModuleDir, $folderPath, $options);
        });

        $options .= "</option>";

        return $options;
    }

}