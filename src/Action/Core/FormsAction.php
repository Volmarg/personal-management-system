<?php

namespace App\Action\Core;

use App\Services\Core\Translator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FormsAction extends AbstractController {

    const TWIG_RENDERED_FORM_TEMPLATE = 'page-elements/components/forms/rendered-form.twig';

    const KEY_FORM_NAMESPACE = 'form_namespace';

    /**
     * @var \App\Services\Core\Translator $translator
     */
    private $translator;

    public function __construct(Translator $translator) {
        $this->translator = $translator;
    }

    /**
     * This function is used on frontend to fetch the form
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/api/get-form-view-by-class-name", name="get_form_view_by_class_name", methods="POST")
     */
    public function getFormViewByClassName(Request $request):JsonResponse {

        if( !$request->request->has(self::KEY_FORM_NAMESPACE) ){
            $message = $this->translator->translate('responses.general.missingRequiredParameter') . self::KEY_FORM_NAMESPACE;

            $data = [
                'error' => $message,
            ];
            return new JsonResponse($data);
        }

        $form_namespace = $request->request->get(self::KEY_FORM_NAMESPACE);

        try{
            $form       = $this->createForm($form_namespace)->createView();
            $form_view  = $this->render(self::TWIG_RENDERED_FORM_TEMPLATE, ['form' => $form] )->getContent();

            $data = [
                'form_view' => $form_view,
            ];

        }catch(\Exception $e){
            $message = $this->translator->translate('forms.general.error.couldNotLoadFormForGivenNamespace');

            $data = [
                'error' => $message,
            ];
        }

        return new JsonResponse($data);
    }

}