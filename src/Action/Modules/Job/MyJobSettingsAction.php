<?php


namespace App\Action\Modules\Job;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyJobSettingsAction extends AbstractController {

    const SETTINGS_TWIG_TEMPLATE = 'modules/my-job/settings.html.twig';
    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/my-job/settings", name="my-job-settings")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {

        $this->addJobHolidayPool($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    public function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $all_holidays_pools      = $this->app->repositories->myJobHolidaysPoolRepository->findBy(['deleted' => 0]);
        $job_holidays_pool_form  = $this->app->forms->jobHolidaysPoolForm();

        $twig_data = [
            'ajax_render'                       => $ajax_render,
            'all_holidays_pools'                => $all_holidays_pools,
            'job_holidays_pool_form'            => $job_holidays_pool_form->createView(),
            'skip_rewriting_twig_vars_to_js'    => $skip_rewriting_twig_vars_to_js
        ];

        return $this->render(static::SETTINGS_TWIG_TEMPLATE, $twig_data);
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
}