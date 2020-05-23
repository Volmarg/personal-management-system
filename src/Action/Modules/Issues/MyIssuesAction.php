<?php

namespace App\Action\Modules\Issues;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyIssuesAction extends AbstractController
{

    const TWIG_TEMPLATE_PENDING_ISSUES = 'modules/my-issues/pending.twig';

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
     * @Route("/my-issues/pending", name="my-issues-pending")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function displayPendingIssues(Request $request) {
        $this->handleIssueForm($request);
        $this->handleIssueContactForm($request);
        $this->handleIssueProgressForm($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate( false);
        }
        $template_content = $this->renderTemplate( true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     * @throws Exception
     */
    public function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false)
    {

        $all_ongoing_issues = $this->app->repositories->myIssueRepository->findAllNotDeletedAndNotResolved();
        $issues_cards_dtos  = $this->controllers->getMyIssuesController()->buildIssuesCardsDtosFromIssues($all_ongoing_issues);

        $data = [
            'ajax_render'                    => $ajax_render,
            'issues_cards_dtos'              => $issues_cards_dtos,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js
        ];

        return $this->render(self::TWIG_TEMPLATE_PENDING_ISSUES, $data);
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
            $this->app->repositories->myIssueRepository->saveIssue($issue);
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
            $issue = $form->getData();
            $this->app->repositories->myIssueProgressRepository->saveIssueProgress($issue);
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
            $issue = $form->getData();
            $this->app->repositories->myIssueContactRepository->saveIssueContact($issue);
        }
    }

}