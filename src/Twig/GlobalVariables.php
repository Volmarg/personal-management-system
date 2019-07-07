<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 16.05.19
 * Time: 20:34
 */

namespace App\Twig;

use App\Controller\Utils\Application;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GlobalVariables extends AbstractExtension {

    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function getFunctions() {
        return [
            new TwigFunction('getMyNotesCategories', [$this, 'getMyNotesCategories']),
        ];
    }

    public function getMyNotesCategories($all = false) {
        $results = $this->app->repositories->myNotesRepository->getCategories($all);
        $new_results = [];

        foreach ($results as $key => $result) {
            $new_results[$result['category_id']] = $result;

            if (!is_null($results[$key]['childrens_id'])) {
                $new_results[$result['category_id']]['childrens_id'] = explode(',', $results[$key]['childrens_id']);
            }
        }

        return $new_results;
    }

}