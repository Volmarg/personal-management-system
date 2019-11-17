<?php

namespace App\Controller\Modules\Contacts;

use App\Controller\Messages\GeneralMessagesController;
use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Entity\Modules\Contacts\MyContactType;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyContactsSettingsController extends AbstractController {

    const TWIG_TEMPLATE_SETTINGS            = 'modules/my-contacts/settings.html.twig';
    const TWIG_TEMPLATE_CONTACT_TYPES_TABLE = 'modules/my-contacts/components/settings/types-settings.table.html.twig';

    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-contacts-settings", name="my-contacts-settings")
     * @param Request $request
     * @return Response
     */
    public function displaySettingsPage(Request $request) {
        $response = $this->submitContactTypeForm($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderSettingsTemplate(false);
        }

        if ($response->getStatusCode() != 200) {
            return $response;
        }
        return $this->renderSettingsTemplate(true);
    }

    public function renderSettingsTemplate($ajax_render = false) {

        $type_form = $this->app->forms->contactType();

        $data = [
          'type_form'           => $type_form->createView(),
          'ajax_render'         => $ajax_render,
          'contact_types_table' => $this->renderContactTypeTemplate()->getContent()
        ];

        return $this->render(self::TWIG_TEMPLATE_SETTINGS, $data);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     */
    public function renderContactTypeTemplate($ajax_render = false) {

        $types = $this->app->repositories->myContactTypeRepository->findBy(['deleted' => 0]);

        $data = [
            'ajax_render' => $ajax_render,
            'types'       => $types,
        ];

        return $this->render(self::TWIG_TEMPLATE_CONTACT_TYPES_TABLE, $data);
    }

    /**
     * @param Request $request
     * @return Response
     */
    private function submitContactTypeForm(Request $request):Response {
        $form = $this->app->forms->contactType();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * @var MyContactType $form_data
             */
            $form_data = $form->getData();
            $name      = $form_data->getName();

            if (!is_null($form_data) && $this->app->repositories->myContactTypeRepository->findBy([ 'name' => $name ] )) {
                return new JsonResponse(GeneralMessagesController::RECORD_WITH_NAME_EXISTS, 409);
            }

            $this->app->em->persist($form_data);
            $this->app->em->flush();
        }

        return new JsonResponse(GeneralMessagesController::FORM_SUBMITTED, 200);
    }

    /**
     * @Route("/my-contacts-types/remove", name="my-contacts-types-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function removeContactType(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_CONTACT_TYPE_REPOSITORY,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            return $this->renderSettingsTemplate(true);
        }
        return $response;
    }

    /**
     * @Route("/my-contacts-types/update",name="my-contacts-types-update")
     * @param Request $request
     * @return Response
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function updateContactType(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myContactTypeRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

}