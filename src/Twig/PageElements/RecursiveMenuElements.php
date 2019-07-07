<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 16.05.19
 * Time: 20:34
 */

namespace App\Twig\PageElements;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RecursiveMenuElements extends AbstractExtension {


    public function getFunctions() {
        return [
            new TwigFunction('unsetChildren', [$this, 'unsetChildren']),
        ];
    }

    public function unsetChildren($array, $children) {

        foreach ($children as $child) {
            unset($array[$child]);
        }

        return $array;

    }

}