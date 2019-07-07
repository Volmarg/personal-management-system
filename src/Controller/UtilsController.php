<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UtilsController extends AbstractController {

    public static function unbase64(string $data) {
        return trim(htmlspecialchars_decode(base64_decode($data)));
    }

}
