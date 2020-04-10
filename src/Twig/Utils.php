<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 16.05.19
 * Time: 20:34
 */

namespace App\Twig;

use App\Controller\Core\Env;
use App\Services\Translator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Utils extends AbstractExtension {


    public function getFunctions() {
        return [
            new TwigFunction('unset', [$this, '_unset']),
            new TwigFunction('keepMenuOpen', [$this, 'keepMenuOpen']),
            new TwigFunction('isDemo', [$this, 'isDemo']),
            new TwigFunction('isMaintenance', [$this, 'isMaintenance']),
            new TwigFunction('jsonDecode', [$this, 'jsonDecode']),
            new TwigFunction('translate', [$this, 'translate']),
        ];
    }

    public function _unset($array, $key) {
        unset($array[$key]);
        return $array;
    }

    /**
     * @param string $curr_url
     * @param string $path_url
     * @param string $searched_string
     * @param mixed $chidlrens_submenu_ids
     * @return string|void
     */
    public function keepMenuOpen(string $curr_url, string $path_url = '', string $searched_string = '', $chidlrens_submenu_ids = '') {
        $dropdown_open_class = 'open';

        if (!empty($path_url) && $curr_url == $path_url) {

            return $dropdown_open_class;
        } elseif (!empty($searched_string) && strstr($curr_url, $searched_string)) {

            return $dropdown_open_class;
        } elseif (!empty($chidlrens_submenu_ids)) {

            /**
             * Info: not a perfect solution, but fine for now
             *  might cause problems if url has some numbers in name (equal to child node id)
             */
            foreach ($chidlrens_submenu_ids as $child_submenu_id) {

                if (strstr($curr_url, $child_submenu_id)) {
                    return $dropdown_open_class;
                }

            }

        }

        return;
    }

    public function isDemo() {
        $is_demo = Env::isDemo();
        return $is_demo;
    }

    public function isMaintenance() {
        $is_maintenance = Env::isMaintenance();
        return $is_maintenance;
    }

    public function jsonDecode(string $json): array {
        $arr = json_decode($json, true);
        return $arr;
    }

    public function translate(string $key){
        $translation = (new Translator())->translate($key);
        return $translation;
    }
}