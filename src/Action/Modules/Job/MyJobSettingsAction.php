<?php


namespace App\Action\Modules\Job;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyJobSettingsAction extends AbstractController {

    const SETTINGS_TWIG_TEMPLATE = 'modules/my-job/settings.html.twig';
    /**
     * @var Application
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
     * @Route("/my-job/settings", name="my-job-settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {

        $this->addJobHolidayPool($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $templateContent = $this->renderTemplate(true)->getContent();
        $ajaxResponse    = new AjaxResponse("", $templateContent);
        $ajaxResponse->setCode(Response::HTTP_OK);
        $ajaxResponse->setPageTitle($this->getJobSettingsPageTitle());

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    public function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {

        $allHolidaysPools    = $this->controllers->getMyJobHolidaysPoolController()->getAllNotDeleted();
        $jobHolidaysPoolForm = $this->app->forms->jobHolidaysPoolForm();

        $twigData = [
            'ajax_render'                       => $ajaxRender,
            'all_holidays_pools'                => $allHolidaysPools,
            'job_holidays_pool_form'            => $jobHolidaysPoolForm->createView(),
            'skip_rewriting_twig_vars_to_js'    => $skipRewritingTwigVarsToJs,
            'page_title'                        => $this->getJobSettingsPageTitle(),
        ];

        return $this->render(static::SETTINGS_TWIG_TEMPLATE, $twigData);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function addJobHolidayPool(Request $request): void {

        $form = $this->app->forms->jobHolidaysPoolForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
        }
    }

    /**
     * Will return job settings page title
     *
     * @return string
     */
    private function getJobSettingsPageTitle(): string
    {
        return $this->app->translator->translate('job.settings.title');
    }
}