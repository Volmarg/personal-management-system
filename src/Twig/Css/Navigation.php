<?php


namespace App\Twig\Css;


use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Navigation extends AbstractExtension{

    public function getFunctions() {
        return [
            new TwigFunction('keepMenuOpen', [$this, 'keepMenuOpen']),
        ];
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
}