<?php

namespace App\Controller\Modules\Contacts;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MyContactsSettingsController extends AbstractController
{
    /**
     * @Route("/modules/contacts/my/contacts/settings", name="modules_contacts_my_contacts_settings")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path'    => 'src/Controller/Modules/Contacts/MyContactsSettingsController.php',
        ]);
    }
}
