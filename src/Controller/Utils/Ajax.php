<?php

namespace App\Controller\Utils;

use App\Controller\UtilsController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class Ajax extends AbstractController {

    /**
     * @Route("/ajax/get-entity/", name="ajax-get-entity")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return mixed
     */
    public static function getEntity(Request $request, EntityManagerInterface $em) {
       /* $namespace = $request->request->get('namespace');
        $id = $request->request->get('id');
        $entity = $em->getRepository($namespace)->find($id);
        $json = json_encode($entity);
        $response = new JsonResponse($json);
        return $response;*/
    }

}