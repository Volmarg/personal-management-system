<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AppController extends Controller
{
    /**
     * @Route("/", name="app_default")
     * This is also main redirect when user logs in
     */
    public function index()
    {
        return $this->redirectToRoute('dashboard');
    }
}
