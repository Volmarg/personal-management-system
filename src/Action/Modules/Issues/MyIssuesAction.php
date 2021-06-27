<?php

namespace App\Action\Modules\Issues;

use App\Annotation\System\ModuleAnnotation;
use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MyIssuesAction
 * @package App\Action\Modules\Issues
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_ISSUES
 * )
 */
class MyIssuesAction extends AbstractController
{

    const TWIG_TEMPLATE_PENDING_ISSUES = 'modules/my-issues/pending.twig';

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
     * @Route("/my-issues/pending", name="my-issues-pending")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function displayPendingIssues(Request $request): Response
    {
        $this->handleIssueForm($request);
        $this->handleIssueContactForm($request);
        $this->handleIssueProgressForm($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate( );
        }
        $templateContent = $this->renderTemplate( true)->getContent();

        $ajaxResponse  = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getIssuesPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     * @throws Exception
     */
    public function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {
        $allOngoingIssues = $this->controllers->getMyIssuesController()->findAllNotDeletedAndNotResolved();
        $issuesCardsDtos  = $this->controllers->getMyIssuesController()->buildIssuesCardsDtosFromIssues($allOngoingIssues);

        $data = [
            'ajax_render'                    => $ajaxRender,
            'issues_cards_dtos'              => $issuesCardsDtos,
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
            'page_title'                     => $this->getIssuesPageTitle(),
        ];

        return $this->render(self::TWIG_TEMPLATE_PENDING_ISSUES, $data);
    }

    /**
     * @Route("/my-issues/update/", name="my-issues-pending-update")
     * @param Request $request
     * @return JsonResponse
     * @throws MappingException
     */
    public function update(Request $request): JsonResponse
    {
        $parameters = $request->request->all();
        $entityId   = trim($parameters['id']);
        $entity     = $this->controllers->getMyIssuesController()->findIssueById($entityId);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @param Request $request
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function handleIssueForm(Request $request): void
    {
        $form = $this->app->forms->issueForm();
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ){
            $issue = $form->getData();
            $this->controllers->getMyIssuesController()->saveIssue($issue);
        }

    }

    /**
     * @param Request $request
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function handleIssueProgressForm(Request $request): void
    {
        $form = $this->app->forms->issueProgressForm();
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ){
            $issueProgress = $form->getData();
            $this->controllers->getMyIssuesController()->saveIssueProgress($issueProgress);
        }
    }

    /**
     * @param Request $request
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function handleIssueContactForm(Request $request): void
    {
        $form = $this->app->forms->issueContactForm();
        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() ){
            $issueContact = $form->getData();
            $this->controllers->getMyIssuesController()->saveIssueContact($issueContact);;
        }
    }

    /**
     * Will return issues page title
     *
     * @return string
     */
    private function getIssuesPageTitle(): string
    {
        return $this->app->translator->translate('issues.title');
    }

}