<?php

namespace App\Controller\Utils;

use App\Services\Files\FileTagger;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Utils extends AbstractController {

    const FLASH_TYPE_DANGER  = "danger";

    const TRUE_AS_STRING  = "true";
    const FALSE_AS_STRING = "false";

    /**
     * @param string $dir
     * @return bool
     */
    public static function removeFolderRecursively(string $dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? static::removeFolderRecursively("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    /**
     * Todo: move to files handling service/controller
     *
     * @param string $source
     * @param string $destination
     * @param FileTagger|null $fileTagger
     * @throws Exception
     */
    public static function copyFiles(string $source, string $destination, ?FileTagger $fileTagger = null) {
        $finder = new Finder();
        $finder->depth('==0');

        if (is_dir($source)) {

            $finder->files()->in($source);

            /**
             * @var $file SplFileInfo
             */
            foreach( $finder->files() as $file ){
                $filepath                 = $file->getPathname();
                $fileExtension            = $file->getExtension();
                $filenameWithoutExtension = $file->getFilenameWithoutExtension();

                $filePathInDestinationFolder = "{$destination}/{$filenameWithoutExtension}.{$fileExtension}";

                if( file_exists($filePathInDestinationFolder) ){
                    $currDateTime     = new \DateTime();
                    $filenameDateTime = $currDateTime->format('Y_m_d_h_i_s');

                    $filePathInDestinationFolder = "{$destination}/{$filenameWithoutExtension}.{$filenameDateTime}.{$fileExtension}";
                }

                copy($filepath, $filePathInDestinationFolder);

                if( !is_null($fileTagger) ){
                    $fileTagger->copyTagsFromPathToNewPath($filepath, $filePathInDestinationFolder);
                }
            }

        }else{
            copy($source, $destination);
        }

    }

    /**
     * Get one random element from array
     * @param array $array
     * @return mixed
     */
    public static function arrayGetRandom(array $array) {
        $index      = array_rand($array);
        $element    = $array[$index];

        return $element;
    }

    /**
     * @param array $data
     * @param int $count
     * @return array
     */
    public static function arrayGetNotRepeatingValuesCount(array $data, int $count) {

        $randoms = [];

        for($x = 0; $x <= $count; $x++) {

            $arrayIndex = array_rand($data);
            $randoms[]  = $data[$arrayIndex];

            unset($data[$arrayIndex]);

            if( empty ($data) ) {
                break;
            }

        }

        return $randoms;
    }

    /**
     * @param string|bool $value
     * @return bool
     * @throws Exception
     */
    public static function getBoolRepresentationOfBoolString($value): bool
    {
        if( is_bool($value) ){
            return $value;
        }elseif( is_string($value) ){

            $allowedValues = [
                self::TRUE_AS_STRING, self::FALSE_AS_STRING
            ];

            if( !in_array($value, $allowedValues) ){
                throw new Exception("Not a bool string");
            }

            return self::TRUE_AS_STRING === $value;
        }else{
            throw new \TypeError("Not allowed type: " . gettype($value) );
        }

    }

    /**
     * Will turn the string version of array into real array. The required syntax is:
     * [\"127.0.0.1\", \"192.168.10.1\"]
     *
     * @param string $stringArray
     * @return array
     * @throws Exception
     */
    public static function getRealArrayForStringArray(string $stringArray): array
    {
        $realArray = json_decode($stringArray);
        if( empty($realArray) ){
            return [];
        }

        if(
            JSON_ERROR_NONE != json_last_error()
            ||  (
                    !empty($stringArray)
                &&  "[]" !== $stringArray
                &&  empty($realArray)
            )
        ){
            throw new Exception("Incorrect syntax of array of restricted ips");
        }

        return $realArray;
    }
}
