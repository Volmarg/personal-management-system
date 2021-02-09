<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 16.05.19
 * Time: 20:30
 */

namespace App\Twig\Core;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TemplatesLoader extends AbstractExtension {

    public function getFunctions() {
        return [
            new TwigFunction('getBaseTemplate', [$this, 'getBaseTemplate']),
        ];
    }

    public function getBaseTemplate(bool $ajaxRender) {

        switch ($ajaxRender) {
            case false:
                return 'base.html.twig';
                break;
            case true:
                return 'blank.html.twig';
                break;
        }
    }

}