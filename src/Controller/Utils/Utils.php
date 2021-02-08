<?php

namespace App\Controller\Utils;

use App\Services\Files\FileTagger;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\HttpFoundation\Response;

class Utils extends AbstractController {

    const FLASH_TYPE_SUCCESS = "success";
    const FLASH_TYPE_DANGER  = "danger";

    const TRUE_AS_STRING  = "true";
    const FALSE_AS_STRING = "false";

    /**
     * @param string $data
     * @return string
     */
    public static function unbase64(string $data) {
        return trim(htmlspecialchars_decode(base64_decode($data)));
    }

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
     * @param Response $response
     * @return string
     */
    public static function getFlashTypeForRequest(Response $response){
        $flashType = ( $response->getStatusCode() === 200 ? self::FLASH_TYPE_SUCCESS : self::FLASH_TYPE_DANGER );
        return $flashType;
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
     * This function will search for forms with names in @param array $keysToFilter
     * This function should be used only when there are more than one forms in the request
     *  otherwise it will filter unwanted data
     * @param array $requestArrays
     * @return array
     * @see $keysToFilter and unset them in $request arrays
     */
    public static function filterRequestForms(array $keysToFilter, array $requestArrays):array {

        foreach($keysToFilter as $key){

            if( array_key_exists($key, $requestArrays) ){
                unset($requestArrays[$key]);
            }

        }

        return $requestArrays;
    }

    /**
     * @param string $class
     * @return string
     */
    public static function formClassToFormPrefix(string $class){
        return StringUtil::fqcnToBlockPrefix($class) ?: '';
    }

    /**
     * Will return the string formatted in the way that symfony does it for fields
     *
     * @param string $dataClass - the class used in `configureOptions` method of Form
     * @param string $fieldName
     * @return string
     */
    public static function fieldIdForSymfonyForm(string $dataClass, string $fieldName): string
    {
        return self::formClassToFormPrefix($dataClass) . '_' . $fieldName;
    }

    /**
     * @return string
     */
    public static function randomHexColor() {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    /**
     * @param array $array
     * @return array
     */
    public static function arrayKeysMulti(array $array): array
    {
        $keys = array();

        foreach ($array as $key => $value) {
            $keys[] = $key;

            if (is_array($value)) {
                $keys = array_merge($keys, self::arrayKeysMulti($value));
            }
        }

        return $keys;
    }

    /**
     * @param array $array
     * @return string
     */
    public static function escapedDoubleQuoteJsonEncode(array $array): string
    {
        $json            = \GuzzleHttp\json_encode($array);
        $singleQuoteJson = str_replace('"','\"' , $json);

        return $singleQuoteJson;
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
     * Returns the class name without namespace
     * @param string $classWithNamespace
     * @return string
     */
    public static function getClassBasename(string $classWithNamespace): string
    {
        $classParts            = explode('\\', $classWithNamespace);
        $classWithoutNamespace = end($classParts);
        return $classWithoutNamespace;
    }

    /**
     * Will round the given value to the nearest (LOWER/DOWN) value provided as second parameter, for example nearest 0.25
     * 1.0, 1.25, 1.5, 1,75 ....
     *
     * @param float $actualValue
     * @param float $roundToRepentanceOf
     * @return float|int
     */
    public static function roundDownToAny(float $actualValue, float $roundToRepentanceOf) {
        if( 0 === $actualValue ){
            return 0;
        }

        return floor($actualValue/$roundToRepentanceOf) * $roundToRepentanceOf;
    }

    /**
     * Will convert seconds to time based format
     * Example: 20:35:15
     *
     * @param int $seconds
     * @return string
     */
    public static function secondsToTimeFormat(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $mins  = floor($seconds / 60 % 60);
        $secs  = floor($seconds % 60);

        $timeFormat = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

        return $timeFormat;
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
