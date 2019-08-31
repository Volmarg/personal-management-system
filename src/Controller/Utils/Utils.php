<?php

namespace App\Controller\Utils;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class Utils extends AbstractController {

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
     * @param string $source
     * @param string $destination
     */
    public static function copyFilesRecursively(string $source, string $destination) {

        if (is_dir($source)) {

            $files = scandir($source);

            foreach ($files as $file) {

                if ($file != "." && $file != ".."){
                    static::copyFilesRecursively("$source/$file", "$destination/$file");
                };

            }

        } else if (file_exists($source)) {
            copy($source, $destination);
        }

    }

    /**
     * @param Response $response
     * @return string
     */
    public static function getFlashTypeForRequest(Response $response){
        $flashType = ( $response->getStatusCode() === 200 ? 'success' : 'danger' );
        return $flashType;
    }

}
