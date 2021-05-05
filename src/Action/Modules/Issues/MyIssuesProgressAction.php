<?php

namespace App\Action\Modules\Issues;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MyIssuesProgressAction
 * @package App\Action\Modules\Issues
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_ISSUES
 * )
 */
class MyIssuesProgressAction extends AbstractController
{

    const PARAMETER_ID = "id";

    /**
     * @var Application $app
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    /**
     * @var MyIssuesAction $myIssuesAction
     */
    private MyIssuesAction $myIssuesAction;

    public function __construct(Application $app, Controllers $controllers, MyIssuesAction $myIssuesAction) {
        $this->app            = $app;
        $this->controllers    = $controllers;
        $this->myIssuesAction = $myIssuesAction;
    }

    /**
     * @Route("/my-issues-progress/update", name="my_issues_progress_update")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function update(Request $request): Response
    {
        $parameters = $request->request->all();
        $id         = $parameters[self::PARAMETER_ID];

        $entity     = $this->controllers->getMyIssueProgressController()->findOneById($id);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @Route("/my-issues-progress/remove", name="my_issues_progress_remove")
     * @param Request $request
     * @return Response
     * 
     * @throws Exception
     */
    public function remove(Request $request): Response
    {
        $id       = $request->request->get(self::PARAMETER_ID);
        $response = $this->app->repositories->deleteById(Repositories::MY_ISSUES_PROGRESS_REPOSITORY, $id);
        $message  = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $renderedTemplate = $this->myIssuesAction->renderTemplate(true, true);
            $templateContent  = $renderedTemplate->getContent();
            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

}