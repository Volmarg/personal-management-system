<?php

namespace App\Services\Files\Parser;

use App\Controller\Utils\ArrayUtils;
use Exception;
use Symfony\Component\Yaml\Yaml;
use TypeError;

/**
 * Handles parsing the yaml files,
 * methods must be static as this logic is used in autoinstaller which is not configured to work with DI
 *
 * Class YamlFileParserService
 */
class YamlFileParserService
{
    const TRANSLATION_FILE_EXTENSION_YAML = "yaml";

    /**
     * Will read the given yaml file and return it content as array
     *
     * @param string $filePath
     * @return array
     * @throws Exception
     */
    public static function getFileContentAsArray(string $filePath): array
    {
        if( !file_exists($filePath) ){
            $message = "Could not open the file, as it does not exist: {$filePath}";
            throw new Exception($message);
        }

        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        if( self:: TRANSLATION_FILE_EXTENSION_YAML !== $fileExtension ){
            $message = "This is not a Yaml file. Got extension: {$fileExtension}";
            throw new Exception($message);
        }

        $fileDataArray = Yaml::parseFile($filePath);
        return $fileDataArray;
    }

    /**
     * Will replace node value of given yaml file, saves the file with new content
     *
     * @param string $replacedNode
     * @param string $newValueOfReplacedNode
     * @param string $filePath
     * @return bool
     */
    public static function replaceArrayNodeValue(string $replacedNode, string $newValueOfReplacedNode, string $filePath): bool
    {
        try{
            $fileDataArray     = self::getFileContentAsArray($filePath);
            $replacedNodeArray = self::buildMultidimensionalArrayStructureForYamlNode($replacedNode, $newValueOfReplacedNode);
            if( empty($replacedNodeArray) ){
                $message = "Something is wrong with replaced node! Got node: {$replacedNode}, could not build replacedNodeArray";
                throw new Exception($message);
            }

            $replacedArray  = array_replace_recursive($fileDataArray, $replacedNodeArray);
            $fileNewContent = Yaml::dump($replacedArray);

            file_put_contents($filePath, $fileNewContent);
            return true;
        }catch(Exception | TypeError $e){
            return false;
        }
    }

    /**
     * Converts the yaml string `string.string` into multidimensional array
     * this conversion of array structure is needed as the parsed yaml file has multidimensional array structure,
     * the values don't matter so empty strings are being inserted.
     *
     * The only important thing here is structure of keys and the ve of last element
     *
     * @param string $yamlNode
     * @param string $newValue
     * @return array
     */
    private static function buildMultidimensionalArrayStructureForYamlNode(string $yamlNode, string $newValue): array
    {
        return ArrayUtils::stringIntoMultidimensionalArray(".", $yamlNode, $newValue);
    }

}