<?php

namespace App\Twig\Core;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Controller\Core\Env as EnvController;

class Env extends AbstractExtension {


    public function getFunctions() {
        return [
            new TwigFunction('unset', [$this, '_unset']),
            new TwigFunction('isDemo', [$this, 'isDemo']),
            new TwigFunction('isMaintenance', [$this, 'isMaintenance']),
        ];
    }

    /**
     * @param $array
     * @param $key
     * @return mixed
     */
    public function _unset($array, $key) {
        unset($array[$key]);
        return $array;
    }

    /**
     * @return bool
     */
    public function isDemo() {
        $is_demo = EnvController::isDemo();
        return $is_demo;
    }

    /**
     * @return bool
     */
    public function isMaintenance() {
        $is_maintenance = EnvController::isMaintenance();
        return $is_maintenance;
    }

}