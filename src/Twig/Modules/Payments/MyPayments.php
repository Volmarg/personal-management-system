<?php

namespace App\Twig\Modules\Payments;

use App\Controller\Core\Application;
use App\Controller\Modules\Notes\MyNotesController;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MyPayments extends AbstractExtension {

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
        ];
    }

    public function calculateHomeProductPrice($product_price, $currency_multiplier) {
        if (preg_match('/^[-+]?[0-9]*\\.?[0-9]+$/', $product_price)) {
            return $product_price * $currency_multiplier;
        }
        return 'Incorrect product price';
    }

}