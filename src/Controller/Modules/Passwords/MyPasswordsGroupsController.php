<?php

namespace App\Controller\Modules\Passwords;

use App\Controller\Messages\GeneralMessagesController;
use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Entity\Modules\Passwords\MyPasswordsGroups;
use App\Form\Modules\Passwords\MyPasswordsGroupsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyPasswordsGroupsController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-passwords-settings", name="my-passwords-settings")
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function display(Request $request) {
        $response = $this->submitForm($this->getGroupsForm(), $request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        if ($response->getStatusCode() != 200) {
            return $response;
        }
        return $this->renderTemplate(true);
    }

    /**
     * @Route("/my-passwords-groups/remove", name="my-passwords-groups-remove")
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_PASSWORDS_GROUPS_REPOSITORY_NAME,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            return $this->renderTemplate(true);
        }
        return $response;
    }

    /**
     * @Route("/my-passwords-groups/update",name="my-passwords-groups-update")
     * @param Request $request
     * @return Response
     */
    public function update(Request $request) {
        $parameters     = $request->request->all();
        $entity         = $this->app->repositories->myPasswordsGroupsRepository->find($parameters['id']);
        $response       = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @param bool $ajax_render
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    private function renderTemplate($ajax_render = false) {

        $groups_form    = $this->getGroupsForm();
        $groups         = $this->app->repositories->myPasswordsGroupsRepository->findBy(['deleted' => 0]);

        return $this->render('modules/my-passwords/settings.html.twig',
            [
                'ajax_render'   => $ajax_render,
                'groups'        => $groups,
                'groups_form'   => $groups_form->createView()
            ]
        );
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return JsonResponse
     */
    private function submitForm(FormInterface $form, Request $request) {
        $form->handleRequest($request);
        /**
         * @var MyPasswordsGroups $form_data
         */
        $form_data = $form->getData();

        if (!is_null($form_data) && $this->app->repositories->myPasswordsGroupsRepository->findBy(['name' => $form_data->getName()])) {
            return new JsonResponse(GeneralMessagesController::RECORD_WITH_NAME_EXISTS, 409);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->app->em->persist($form_data);
            $this->app->em->flush();
        }

        return new JsonResponse(GeneralMessagesController::FORM_SUBMITTED, 200);
    }

    private function getGroupsForm() {
        return $this->createForm(MyPasswordsGroupsType::class);
    }


}
