<?php

namespace App\Controller\Modules\Contacts;

use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Form\Modules\Contacts\MyContactsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MyContactsController extends AbstractController {
    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

    /**
     * @Route("/my-contacts/{type?}", name="my-contacts")
     * @param Request $request
     * @param $type
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function display(Request $request, $type) {

        if (is_null($type)) {
            return $this->redirectToRoute('my-contacts', ['type' => 'phone']);
        }

        $this->addFormDataToDB($this->getForm($type), $request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate($type, false);
        }
        return $this->renderTemplate($type, true);
    }

    protected function renderTemplate($type, $ajax_render = false) {

        $form       = $this->getForm($type);
        $form_view  = $form->createView();
        $contacts   = $this->app->repositories->myContactsRepository->findBy(['type' => $type, 'deleted' => 0]);
        $groups     = $this->app->repositories->myContactsGroupsRepository->findBy(['deleted' => 0]);

        return $this->render('modules/my-contacts/my-contacts.html.twig', [
            'form' => $form_view,
            'ajax_render' => $ajax_render,
            'contacts' => $contacts,
            'type' => $type,
            'groups' => $groups
        ]);

    }

    /**
     * @param $form
     * @param $request
     */
    protected function addFormDataToDB($form, $request) {
        $form->handleRequest($request);

        if ($form->isSubmitted($request) && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
        }

    }

    /**
     * @Route("/my-contacts/remove/{type?}", name="my-contacts-remove")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function remove(Request $request, $type) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_CONTACTS_REPOOSITORY_NAME,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            return $this->renderTemplate($type, true);
        }
        return $response;
    }

    /**
     * @Route("my-contacts/update/{type?}" ,name="my-contacts-update")
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myContactsRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    private function getForm($type) {

        return $this->createForm(MyContactsType::class, null, ['type' => $type]);
    }
}
