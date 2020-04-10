<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 16.05.19
 * Time: 20:34
 */

namespace App\Twig;

use App\Controller\Core\Application;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Calculations extends AbstractExtension {

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function getFunctions() {
        return [
            new TwigFunction('calculateHomeProductPrice', [$this, 'calculateHomeProductPrice']),
            new TwigFunction('isCategoryActive', [$this, 'isCategoryActive']),
        ];
    }

    public function calculateHomeProductPrice($product_price, $currency_multiplier) {
        if (preg_match('/^[-+]?[0-9]*\\.?[0-9]+$/', $product_price)) {
            return $product_price * $currency_multiplier;
        }
        return 'Incorrect product price';
    }

    /**
     * Based on count of notes in category (for recursive menu mostly)
     * @param int $category_id
     * @param string $type
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function isCategoryActive(int $category_id, string $type){

        switch ($type) {
            case 'MyNotes':
                return (bool) $this->app->repositories->myNotesRepository->countNotesInCategoryByCategoryId($category_id);
            default:
                return false;
        }

    }

}