<?php

namespace App\Action\Modules\Travels;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Controller\Utils\Utils;
use App\Entity\Modules\Travels\MyTravelsIdeas;
use App\Form\Modules\Travels\MyTravelsIdeasType;
use Doctrine\DBAL\DBALException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyTravelsIdeasAction extends AbstractController {

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @return FormInterface
     * @throws DBALException
     */
    private function getForm() {
        $categories = $this->controllers->getMyTravelsIdeasController()->getAllCategories(true);
        $travel_ideas_form = $this->app->forms->travelIdeasForm(['categories' => $categories]);
        return $travel_ideas_form;
    }

    /**
     * @Route("/my-travels/ideas", name="my-travels-ideas")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request) {
        $this->addFormDataToDB($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     * @throws DBALException
     */
    protected function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {
        $form      = $this->getForm();
        $form_view = $form->createView();

        $columns_names = $this->app->em->getClassMetadata(MyTravelsIdeas::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($columns_names);

        $all_ideas  = $this->controllers->getMyTravelsIdeasController()->getAllNotDeleted();
        $categories = $this->controllers->getMyTravelsIdeasController()->getAllCategories();

        $data = [
            'form_view'     => $form_view,
            'columns_names' => $columns_names,
            'all_ideas'     => $all_ideas,
            'ajax_render'   => $ajax_render,
            'categories'    => $categories,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ];

        return $this->render('modules/my-travels/ideas.html.twig', $data);
    }

    /**
     * @param $request
     * @return void
     */
    protected function addFormDataToDB(Request $request): void {

        $form = $this->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $travel_idea = $form->getData();

            $this->app->em->persist($travel_idea);
            $this->app->em->flush();
        }
    }

    /**
     * @Route("/my-travels/ideas/update/",name="my-travels-ideas-update")
     * @param Request $request
     * @return Response
     *
     * @throws Exception
     */
    public function update(Request $request)
    {
        $response_code = Response::HTTP_OK;
        $message       = $this->app->translator->translate('responses.repositories.recordUpdateSuccess');

        try{
            $id = trim($request->request->get('id', null));

            if( empty($id) ){
                $message = "Parameter `id` is either missing or is malformed";
                $this->app->logger->critical($message, [
                    "id" => $id
                ]);
                throw new Exception($message);
            }

            $existing_entity = $this->controllers->getMyTravelsIdeasController()->findOneById($id);
            if( empty($existing_entity) ){
                $message = "There is no entity for given entity id: {$id}";
                $this->app->logger->critical($message);
                throw new Exception($message);
            }

            // Whole form is being sent as serialized data - must be turned back to array
            $travel_idea_form = $this->getForm();
            $all_request_data = $request->request->all();

            if( !array_key_exists(Repositories::KEY_SERIALIZED_FORM_DATA, $all_request_data) ){
                $message = "Data from request does not belong to processed form - wrong prefix has been used, probably incorrect form is being used on backend";
                $this->app->logger->critical($message);
                throw new Exception($message);
            }

            $travel_idea_form_serialized_data = $all_request_data[Repositories::KEY_SERIALIZED_FORM_DATA];
            parse_str($travel_idea_form_serialized_data, $form_data_array_with_form_prefix);

            // set data back to request to return entity from form
            $travel_idea_form_prefix = Utils::formClassToFormPrefix(MyTravelsIdeas::class);
            $travel_idea_form_data   = $form_data_array_with_form_prefix[$travel_idea_form_prefix];

            $request->request->set($travel_idea_form_prefix, $travel_idea_form_data);

            /**
             * @var MyTravelsIdeas $modified_entity
             */
            $modified_entity = $travel_idea_form->handleRequest($request)->getData();

            // set new properties to existing entity to prevent saving new one upon submitting form
            $existing_entity->setMap($modified_entity->getMap());
            $existing_entity->setLocation($modified_entity->getLocation());
            $existing_entity->setImage($modified_entity->getImage());
            $existing_entity->setCountry($modified_entity->getCountry());
            $existing_entity->setCategory($modified_entity->getCategory());

            $this->controllers->getMyTravelsIdeasController()->save($existing_entity);
        }catch(Exception $e){
            $message       = $this->app->translator->translate('responses.repositories.recordUpdateFail');
            $response_code = Response::HTTP_INTERNAL_SERVER_ERROR;
            $this->app->logExceptionWasThrown($e);
        }

        $rendered_template = $this->renderTemplate(true)->getContent();

        return AjaxResponse::buildJsonResponseForAjaxCall($response_code, $message, $rendered_template);
    }

    /**
     * @Route("/my-travels/ideas/remove/",name="my-travels-ideas-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request) {
        $id         = trim($request->request->get('id'));
        $response   = $this->app->repositories->deleteById(
            Repositories::MY_TRAVELS_IDEAS_REPOSITORY_NAME,
            $id
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true, true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

}