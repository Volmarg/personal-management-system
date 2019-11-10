<?php

namespace App\Controller\Modules\Contacts2;

use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyContactsController extends AbstractController {

    const TWIG_TEMPLATE = 'modules/my-contacts-2/my-contacts.html.twig';

    const KEY_CONTACTS    = 'contacts';
    const KEY_AJAX_RENDER = 'ajax_render';

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-contacts-2", name="my-contacts-2")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function display(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate( false);
        }
        return $this->renderTemplate( true);
    }

    protected function renderTemplate($ajax_render = false) {

        $contacts = $this->app->repositories->myContactRepository->findAllNotDeleted();

        $data = [
            self::KEY_AJAX_RENDER   => $ajax_render,
            self::KEY_CONTACTS      => $contacts
        ];

        return $this->render(self::TWIG_TEMPLATE, $data);
    }

    /**
     * @Route("/my-contacts-2/remove", name="my-contacts-2-remove")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_CONTACT_REPOSITORY,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            return $this->renderTemplate(true);
        }
        return $response;
    }

    /**
     * @Route("my-contacts-2/update" ,name="my-contacts-2-update")
     * @param Request $request
     * @return JsonResponse
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entity     = $this->app->repositories->myContactRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

}
