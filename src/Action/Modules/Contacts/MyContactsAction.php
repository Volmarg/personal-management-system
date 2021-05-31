<?php

namespace App\Action\Modules\Contacts;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Controller\Utils\Utils;
use App\DTO\Modules\Contacts\ContactsTypesDTO;
use App\DTO\Modules\Contacts\ContactTypeDTO;
use App\Entity\Modules\Contacts\MyContact;
use App\Form\Modules\Contacts\MyContactType;
use App\Form\Modules\Contacts\MyContactTypeDtoType;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyContactsAction extends AbstractController
{

    const TWIG_TEMPLATE = 'modules/my-contacts/my-contacts.html.twig';

    const KEY_CONTACTS    = 'contacts';
    const KEY_AJAX_RENDER = 'ajax_render';
    const KEY_TYPE        = 'type';

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers  $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/my-contacts", name="my-contacts")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate( );
        }
        $templateContent = $this->renderTemplate( true)->getContent();

        $ajaxResponse = new AjaxResponse("", $templateContent);
        $ajaxResponse->setPageTitle($this->getContactsPageTitle());
        $ajaxResponse->setCode(Response::HTTP_OK);

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("/my-contacts/remove", name="my-contacts-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {
        $response = $this->app->repositories->deleteById(
            Repositories::MY_CONTACT_REPOSITORY,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderTemplate(true, true);
            $templateContent  = $renderedTemplate->getContent();
            $message          = $this->app->translator->translate('messages.ajax.success.recordHasBeenRemoved');

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }

        $message = $this->app->translator->translate('messages.ajax.failure.couldNotRemoveRecord');
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * This is special case where we have to manipulate data from ajax as form can have additional fields
     * thus there is this one method to handle both action as the logic is the same
     * @Route("my-contacts/update" ,name="my-contacts-update")
     * @param Request $request
     * @return JsonResponse
     * @throws DBALException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function update(Request $request) {
        $contactFormData = [];

        // transform js serialized form back to array
        $contactFormPrefix         = Utils::formClassToFormPrefix(MyContactType::class);
        $contactFormSerializedData = $request->request->get($contactFormPrefix);

        parse_str($contactFormSerializedData, $contactFormData);

        // and replace it for form handling
        $request->request->set($contactFormPrefix, $contactFormData);

        $this->handleForms($request);
        $templateContent = $this->renderTemplate( true)->getContent();
        $message         = $this->app->translator->translate('responses.repositories.recordUpdateSuccess');

        return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
    }

    /**
     * @param Request $request
     * @throws DBALException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    private function handleForms(Request $request){

        $contactFormPrefix = Utils::formClassToFormPrefix(MyContactType::class);
        $allRequestParams  = $request->request->all();

        if( empty($allRequestParams) ){
            return;
        }

        $forms           = $allRequestParams[$contactFormPrefix];
        $contactFormData = $forms[$contactFormPrefix];

        $filteredTypesForms = Utils::filterRequestForms([$contactFormPrefix], $forms);

        // build request for processing the main form
        $request = new Request();
        $request->request->set($contactFormPrefix, $contactFormData);

        $contactForm = $this->app->forms->contactForm()->handleRequest($request);
        $contactForm->submit($contactFormData);

        if( $contactForm->isSubmitted() && $contactForm->isValid() ){

            $arrayOfContactsTypesDtos = [];

            // Build contacts from all passed in forms
            foreach( $filteredTypesForms as $uuid => $form ){

                if( !array_key_exists(MyContactTypeDtoType::KEY_NAME, $form) ){
                    throw new Exception("");
                }elseif( !array_key_exists(MyContactTypeDtoType::KEY_TYPE, $form) ){
                    throw new Exception("");
                }

                $typeDetails = $form[MyContactTypeDtoType::KEY_NAME];
                $typeId      = $form[MyContactTypeDtoType::KEY_TYPE];

                $iconPath = $this->controllers->getMyContactTypeController()->getImagePathForById($typeId);
                $typeName = $this->controllers->getMyContactTypeController()->getTypeNameById($typeId);

                if( empty($iconPath) ){
                    throw new Exception("");
                }

                $contactTypeDto = new ContactTypeDTO();
                $contactTypeDto->setDetails($typeDetails);
                $contactTypeDto->setName($typeName);
                $contactTypeDto->setIconPath($iconPath);
                $contactTypeDto->setUuid($uuid);

                $arrayOfContactsTypesDtos[] = $contactTypeDto;

            }

            $contactsTypesDto = new ContactsTypesDTO();
            $contactsTypesDto->setContactTypeDtos($arrayOfContactsTypesDtos);
            $contactsJson = $contactsTypesDto->toJson();

            $myContact = $contactForm->getData();
            if( !$myContact instanceof MyContact ){
                throw new Exception('');
            }

            $myContact->setContacts($contactsJson);

            $normalizedDescriptionColor = str_replace("#", "", $myContact->getDescriptionBackgroundColor());
            $normalizedNameColor        = str_replace("#", "", $myContact->getNameBackgroundColor());

            $myContact->setNameBackgroundColor($normalizedDescriptionColor);
            $myContact->setDescriptionBackgroundColor($normalizedNameColor);

            $this->controllers->getMyContactController()->saveEntity($myContact, true);
        }

    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return mixed
     */
    private function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {
        $contacts = $this->controllers->getMyContactController()->findAllNotDeleted();

        $data = [
            self::KEY_AJAX_RENDER            => $ajaxRender,
            self::KEY_CONTACTS               => $contacts,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'page_title'                     => $this->getContactsPageTitle(),
        ];

        return $this->render(self::TWIG_TEMPLATE, $data);
    }

    /**
     * Will return the contacts page title
     *
     * @return string
     */
    private function getContactsPageTitle(): string
    {
        return $this->app->translator->translate('contact.title');
    }

}