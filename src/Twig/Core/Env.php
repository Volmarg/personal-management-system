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
            new TwigFunction('isGuide', [$this, 'isGuide']),
            new TwigFunction('isMaintenance', [$this, 'isMaintenance']),
            new TwigFunction('areInfoBlocksShown', [$this, 'areInfoBlocksShown']),
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
        $isDemo = EnvController::isDemo();
        return $isDemo;
    }

    /**
     * @return bool
     */
    public function isMaintenance() {
        $isMaintenance = EnvController::isMaintenance();
        return $isMaintenance;
    }

    /**
     * @return bool
     */
    public function isGuide(): bool
    {
        $isGuide = EnvController::isGuide();
        return $isGuide;
    }

    /**
     * @return bool
     */
    public function areInfoBlocksShown(): bool
    {
        $areInfoBlocksShown = EnvController::areInfoBlocksShown();
        return $areInfoBlocksShown;
    }

}