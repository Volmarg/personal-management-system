<?php

namespace App\Action\Modules\Contacts;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
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
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-contacts", name="my-contacts")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request) {
        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate( false);
        }
        $template_content  = $this->renderTemplate( true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/my-contacts/remove", name="my-contacts-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_CONTACT_REPOSITORY,
            $request->request->get('id')
        );

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true, true);
            $template_content  = $rendered_template->getContent();
            $message           = $this->app->translator->translate('messages.ajax.success.recordHasBeenRemoved');

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }

        $message = $this->app->translator->translate('messages.ajax.failure.couldNotRemoveRecord');

        return AjaxResponse::buildResponseForAjaxCall(500, $message);
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
     */
    public function update(Request $request) {
        $contact_form_data = [];

        // transform js serialized form back to array
        $contact_form_prefix          = Utils::formClassToFormPrefix(MyContactType::class);
        $contact_form_serialized_data = $request->request->get($contact_form_prefix);

        parse_str($contact_form_serialized_data, $contact_form_data);

        // and replace it for form handling
        $request->request->set($contact_form_prefix, $contact_form_data);

        $this->handleForms($request);
        $template_content = $this->renderTemplate( true)->getContent();
        $message          = $this->app->translator->translate('responses.repositories.recordUpdateSuccess');

        return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
    }

    /**
     * @param Request $request
     * @throws DBALException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    private function handleForms(Request $request){

        $contact_form_prefix   = Utils::formClassToFormPrefix(MyContactType::class);
        $all_request_params    = $request->request->all();

        if( empty($all_request_params) ){
            return;
        }

        $forms             = $all_request_params[$contact_form_prefix];
        $contact_form_data = $forms[$contact_form_prefix];

        $filtered_types_forms = Utils::filterRequestForms([$contact_form_prefix], $forms);

        // build request for processing the main form
        $request = new Request();
        $request->request->set($contact_form_prefix, $contact_form_data);

        $contact_form = $this->app->forms->contactForm()->handleRequest($request);
        $contact_form->submit($contact_form_data);

        if( $contact_form->isSubmitted() && $contact_form->isValid() ){

            $array_of_contacts_types_dtos = [];

            // Build contacts from all passed in forms

            foreach( $filtered_types_forms as $uuid => $form ){

                if( !array_key_exists(MyContactTypeDtoType::KEY_NAME, $form) ){
                    $message = '';
                    throw new Exception($message);
                }elseif( !array_key_exists(MyContactTypeDtoType::KEY_TYPE, $form) ){
                    $message = '';
                    throw new Exception($message);
                }

                $type_details   = $form[MyContactTypeDtoType::KEY_NAME];
                $type_id        = $form[MyContactTypeDtoType::KEY_TYPE];

                $icon_path   = $this->app->repositories->myContactTypeRepository->getImagePathForTypeById($type_id);
                $type_name   = $this->app->repositories->myContactTypeRepository->getTypeNameTypeById($type_id);

                if( empty($icon_path) ){
                    $message = '';
                    throw new Exception($message);
                }

                $contact_type_dto = new ContactTypeDTO();
                $contact_type_dto->setDetails($type_details);
                $contact_type_dto->setName($type_name);
                $contact_type_dto->setIconPath($icon_path);
                $contact_type_dto->setUuid($uuid);

                $array_of_contacts_types_dtos[] = $contact_type_dto;

            }

            $contacts_types_dto = new ContactsTypesDTO();
            $contacts_types_dto->setContactTypeDtos($array_of_contacts_types_dtos);
            $contacts_json = $contacts_types_dto->toJson();

            /**
             * @var MyContact $my_contact
             */
            $my_contact = $contact_form->getData();

            if( !$my_contact instanceof MyContact ){
                $message = '';
                throw new Exception($message);
            }

            $my_contact->setContacts($contacts_json);

            $this->app->repositories->myContactRepository->saveEntity($my_contact, true);
        }

    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return mixed
     */
    private function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $contacts = $this->app->repositories->myContactRepository->findAllNotDeleted();

        $data = [
            self::KEY_AJAX_RENDER            => $ajax_render,
            self::KEY_CONTACTS               => $contacts,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js
        ];

        return $this->render(self::TWIG_TEMPLATE, $data);
    }

}