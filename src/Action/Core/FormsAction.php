<?php

namespace App\Action\Core;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Services\Core\Translator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormsAction extends AbstractController {

    const TWIG_RENDERED_FORM_TEMPLATE = 'page-elements/components/forms/rendered-form.twig';

    const KEY_FORM_NAMESPACE = 'form_namespace';

    /**
     * @var Translator $translator
     */
    private $translator;

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Translator $translator, Application $app) {
        $this->translator = $translator;
        $this->app        = $app;
    }

    /**
     * This function is used on frontend to fetch the form
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/api/get-form-view-by-class-name", name="get_form_view_by_class_name", methods="POST")
     */
    public function getFormViewByClassName(Request $request): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();
        $code         = Response::HTTP_OK;
        $formTemplate = "";
        $message      = "";
        $success      = true;

        try{
            if( !$request->request->has(self::KEY_FORM_NAMESPACE) ){
                $message = $this->translator->translate('responses.general.missingRequiredParameter') . self::KEY_FORM_NAMESPACE;

                $ajaxResponse->setMessage($message);
                $ajaxResponse->setSuccess(false);
                $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
                $jsonResponse = $ajaxResponse->buildJsonResponse();
                return $jsonResponse;
            }

            $formNamespace = $request->request->get(self::KEY_FORM_NAMESPACE);
            $form          = $this->createForm($formNamespace)->createView();
            $formTemplate  = $this->render(self::TWIG_RENDERED_FORM_TEMPLATE, ['form' => $form] )->getContent();

        }catch(Exception $e){
            $message = $this->translator->translate('forms.general.error.couldNotLoadFormForGivenNamespace');
            $code    = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $this->app->logExceptionWasThrown($e);
        }

        $ajaxResponse->setCode($code);
        $ajaxResponse->setMessage($message);
        $ajaxResponse->setFormTemplate($formTemplate);
        $ajaxResponse->setSuccess($success);

        $jsonResponse = $ajaxResponse->buildJsonResponse();

        return $jsonResponse;
    }

}