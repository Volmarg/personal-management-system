<?php

namespace App\Action\Modules\Travels;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Controller\Utils\Utils;
use App\Entity\Modules\Travels\MyTravelsIdeas;
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
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @return FormInterface
     * @throws DBALException
     */
    private function getForm() {
        $categories      = $this->controllers->getMyTravelsIdeasController()->getAllCategories(true);
        $travelIdeasForm = $this->app->forms->travelIdeasForm(['categories' => $categories]);
        return $travelIdeasForm;
    }

    /**
     * @Route("/my-travels/ideas", name="my-travels-ideas")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {
        $this->addFormDataToDB($request);
        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent = $this->renderTemplate(true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getTravelsIdeasPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     * @throws DBALException
     */
    protected function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {
        $form     = $this->getForm();
        $formView = $form->createView();

        $columnsNames = $this->app->em->getClassMetadata(MyTravelsIdeas::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($columnsNames);

        $allIdeas   = $this->controllers->getMyTravelsIdeasController()->getAllNotDeleted();
        $categories = $this->controllers->getMyTravelsIdeasController()->getAllCategories();

        $data = [
            'form_view'     => $formView,
            'columns_names' => $columnsNames,
            'all_ideas'     => $allIdeas,
            'ajax_render'   => $ajaxRender,
            'categories'    => $categories,
            'page_title'    => $this->getTravelsIdeasPageTitle(),
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
        ];

        return $this->render('modules/my-travels/ideas.html.twig', $data);
    }

    /**
     * @param $request
     * @return void
     * @throws DBALException
     */
    protected function addFormDataToDB(Request $request): void {

        $form = $this->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $travelIdea = $form->getData();

            $this->app->em->persist($travelIdea);
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
    public function update(Request $request): Response
    {
        $responseCode = Response::HTTP_OK;
        $message      = $this->app->translator->translate('responses.repositories.recordUpdateSuccess');

        try{
            $id = trim($request->request->get('id', null));

            if( empty($id) ){
                $message = "Parameter `id` is either missing or is malformed";
                $this->app->logger->critical($message, [
                    "id" => $id
                ]);
                throw new Exception($message);
            }

            $existingEntity = $this->controllers->getMyTravelsIdeasController()->findOneById($id);
            if( empty($existingEntity) ){
                $message = "There is no entity for given entity id: {$id}";
                $this->app->logger->critical($message);
                throw new Exception($message);
            }

            // Whole form is being sent as serialized data - must be turned back to array
            $travelIdeaForm = $this->getForm();
            $allRequestData = $request->request->all();

            if( !array_key_exists(Repositories::KEY_SERIALIZED_FORM_DATA, $allRequestData) ){
                $message = "Data from request does not belong to processed form - wrong prefix has been used, probably incorrect form is being used on backend";
                $this->app->logger->critical($message);
                throw new Exception($message);
            }

            $travelIdeaFormSerializedData = $allRequestData[Repositories::KEY_SERIALIZED_FORM_DATA];
            parse_str($travelIdeaFormSerializedData, $formDataArrayWithFormPrefix);

            // set data back to request to return entity from form
            $travelIdeaFormPrefix = Utils::formClassToFormPrefix(MyTravelsIdeas::class);
            $travelIdeaFormData   = $formDataArrayWithFormPrefix[$travelIdeaFormPrefix];

            $request->request->set($travelIdeaFormPrefix, $travelIdeaFormData);

            /**
             * @var MyTravelsIdeas $modifiedEntity
             */
            $modifiedEntity = $travelIdeaForm->handleRequest($request)->getData();

            // set new properties to existing entity to prevent saving new one upon submitting form
            $existingEntity->setMap($modifiedEntity->getMap());
            $existingEntity->setLocation($modifiedEntity->getLocation());
            $existingEntity->setImage($modifiedEntity->getImage());
            $existingEntity->setCountry($modifiedEntity->getCountry());
            $existingEntity->setCategory($modifiedEntity->getCategory());

            $this->controllers->getMyTravelsIdeasController()->save($existingEntity);
        }catch(Exception $e){
            $message       = $this->app->translator->translate('responses.repositories.recordUpdateFail');
            $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $this->app->logExceptionWasThrown($e);
        }

        $renderedTemplate = $this->renderTemplate(true)->getContent();

        return AjaxResponse::buildJsonResponseForAjaxCall($responseCode, $message, $renderedTemplate);
    }

    /**
     * @Route("/my-travels/ideas/remove/",name="my-travels-ideas-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {
        $id         = trim($request->request->get('id'));
        $response   = $this->app->repositories->deleteById(
            Repositories::MY_TRAVELS_IDEAS_REPOSITORY_NAME,
            $id
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderTemplate(true, true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * Will return travels ideas page title
     *
     * @return string
     */
    private function getTravelsIdeasPageTitle(): string
    {
        return $this->app->translator->translate('travels.title');
    }

}