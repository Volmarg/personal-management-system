<?php


namespace App\Action\Modules\Payments;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Repositories;
use Doctrine\DBAL\DBALException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyPaymentsOwedAction extends AbstractController {


    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {

        $this->app = $app;
    }

    /**
     * @Route("/my-payments-owed", name="my-payments-owed")
     * @param Request $request
     * @return Response
     * @throws DBALException
     */
    public function display(Request $request) {
        $this->add($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/my-payments-owed/remove/", name="my-payments-owed-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request) {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_PAYMENTS_OWED_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true, true);
            $template_content  = $rendered_template->getContent();

            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }
        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @Route("my-payments-owed/update/" ,name="my-payments-owed-update")
     * @param Request $request
     * @return JsonResponse
     * 
     */
    public function update(Request $request) {
        $parameters     = $request->request->all();
        $entity         = $this->app->repositories->myPaymentsOwedRepository->find($parameters['id']);
        $response       = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     * @throws DBALException
     */
    private function renderTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {

        $form           = $this->app->forms->moneyOwedForm();
        $owed_by_me     = $this->app->repositories->myPaymentsOwedRepository->findBy([
            'deleted'  => 0,
            'owedByMe' => 1
        ]);

        $owed_by_others = $this->app->repositories->myPaymentsOwedRepository->findBy([
            'deleted'  => 0,
            'owedByMe' => 0
        ]);

        $summary_owed_by_others = $this->app->repositories->myPaymentsOwedRepository->getMoneyOwedSummaryForTargetsAndOwningSide(0);
        $summary_owed_by_me     = $this->app->repositories->myPaymentsOwedRepository->getMoneyOwedSummaryForTargetsAndOwningSide(1);
        $summary_overall        = $this->app->repositories->myPaymentsOwedRepository->fetchSummaryWhoOwesHowMuch();

        $summary_overall_owed_by_me     = [];
        $summary_overall_owed_by_others = [];

        foreach( $summary_overall as $summary ){
            $is_summary_owed_by_me = $summary['summaryOwedByMe'];

            if($is_summary_owed_by_me){
                $summary_overall_owed_by_me[] = $summary;
                continue;
            }

            $summary_overall_owed_by_others[] = $summary;
        }

        $currencies_dtos = $this->app->settings->settings_loader->getCurrenciesDtosForSettingsFinances();

        return $this->render('modules/my-payments/owed.html.twig', [
            'ajax_render'       => $ajax_render,
            'form'              => $form->createView(),
            'owed_by_me'        => $owed_by_me,
            'owed_by_others'    => $owed_by_others,
            'summary_owed_by_others' => $summary_owed_by_others,
            'summary_owed_by_me'     => $summary_owed_by_me,
            'currencies_dtos'        => $currencies_dtos,
            'summary_overall_owed_by_me'     => $summary_overall_owed_by_me,
            'summary_overall_owed_by_others' => $summary_overall_owed_by_others,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ]);
    }

    /**
     * @param Request $request
     */
    private function add(Request $request) {
        $form = $this->app->forms->moneyOwedForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $form_data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($form_data);
            $em->flush();
        }

    }

}