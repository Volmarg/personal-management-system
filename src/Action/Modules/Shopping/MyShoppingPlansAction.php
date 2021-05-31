<?php

namespace App\Action\Modules\Shopping;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Shopping\MyShoppingPlans;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyShoppingPlansAction extends AbstractController {

    /**
     * @var Application
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
     * @Route("/my-shopping/plans", name="my-shopping-plans")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {
        $shoppingPlanForm = $this->app->forms->myShoppingPlanForm();
        $this->addFormDataToDB($shoppingPlanForm, $request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate();
        }

        $templateContent = $this->renderTemplate(true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getShoppingPlansPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    protected function renderTemplate($ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {
        $form          = $this->app->forms->myShoppingPlanForm();
        $plansFormView = $form->createView();
        $columnsNames  = $this->getDoctrine()->getManager()->getClassMetadata(MyShoppingPlans::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($columnsNames);

        $allPlans = $this->app->repositories->myShoppingPlansRepository->findBy(['deleted' => 0]);

        return $this->render('modules/my-shopping/plans.html.twig', [
            'plans_form_view'                => $plansFormView,
            'columns_names'                  => $columnsNames,
            'all_plans'                      => $allPlans,
            'ajax_render'                    => $ajaxRender,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'page_title'                     => $this->getShoppingPlansPageTitle(),
        ]);
    }

    /**
     * @param $plansForm
     * @param Request $request
     */
    protected function addFormDataToDB($plansForm, Request $request): void {
        $plansForm->handleRequest($request);

        if ($plansForm->isSubmitted() && $plansForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($plansForm->getData());
            $em->flush();
        }
    }

    /**
     * @Route("/my-shopping/plans/update/",name="my-shopping-plans-update")
     * @param Request $request
     * @return JsonResponse|Response
     *
     * @throws MappingException
     */
    public function update(Request $request) {
        $parameters = $request->request->all();
        $entityId   = trim($parameters['id']);

        $entity     = $this->controllers->getMyShoppingPlansController()->findOneById($entityId);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @Route("/my-shopping/plans/remove/",name="my-shopping-plans-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {
        $id = $request->request->get('id');

        $response = $this->app->repositories->deleteById(
            Repositories::MY_SHOPPING_PLANS_REPOSITORY_NAME,
            $id
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->renderTemplate(true);
            $templateContent  = $renderedTemplate->getContent();

            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }
        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * Will return shopping plans page title
     *
     * @return string
     */
    private function getShoppingPlansPageTitle(): string
    {
        return $this->app->translator->translate('shopping.title');
    }

}