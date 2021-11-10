<?php

namespace App\Action\Modules\Job;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Job\MyJobAfterhours;
use App\Form\Modules\Job\MyJobAfterhoursType;
use App\Repository\Modules\Job\MyJobAfterhoursRepository;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class MyJobAfterhoursAction extends AbstractController {

    /**
     * @var array $entityEnums
     */
    private array $entityEnums = [];

    /**
     * @var Application
     */
    private Application $app;

    /**
     * @var Controllers $controllers
     */
    private Controllers $controllers;

    public function __construct(Application $app, Controllers $controllers) {
        $entityEnums = [MyJobAfterhours::TYPE_MADE, MyJobAfterhours::TYPE_SPENT];

        $this->entityEnums = array_combine(
            array_map('ucfirst', array_values($entityEnums)),
            $entityEnums
        );

        $this->app         = $app;
        $this->controllers = $controllers;
    }


    /**
     * @Route("/my-job/afterhours", name="my-job-afterhours")
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
        $ajaxResponse->setPageTitle($this->getJobAfterhoursPageTitle());
        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    private function renderTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {

        $form               = $this->getForm();
        $afterhoursFormView = $form->createView();

        $columnNames = $this->getDoctrine()->getManager()->getClassMetadata(MyJobAfterhours::class)->getColumnNames();
        Repositories::removeHelperColumnsFromView($columnNames);

        $afterhoursAll = $this->controllers->getMyJobAfterhoursController()->findAllNotDeletedByType([
            MyJobAfterhours::TYPE_SPENT,
            MyJobAfterhours::TYPE_MADE
        ]);

        $afterhoursSpent = $this->controllers->getMyJobAfterhoursController()->findAllNotDeletedByType([
            MyJobAfterhours::TYPE_SPENT
        ]);

        $afterhoursMade = $this->controllers->getMyJobAfterhoursController()->findAllNotDeletedByType([
            MyJobAfterhours::TYPE_MADE
        ]);

        $hoursAll   = 0;
        $secondsAll = 0;
        foreach($afterhoursAll as $afterhour)
        {
            if( MyJobAfterhours::TYPE_MADE === $afterhour->getType() ){
                $secondsAll += $afterhour->getSeconds();
                $hoursAll   += $afterhour->getHours();
            }else{
                $secondsAll -= $afterhour->getSeconds();
                $hoursAll   -= $afterhour->getHours();
            }
        }

        $daysAll                     = $hoursAll / 8; // 8h working day
        $remainingTimeToSpendPerGoal = $this->controllers->getMyJobAfterhoursController()->getTimeToSpend();

        $twigData = [
            'afterhours_form_view'              => $afterhoursFormView,
            'column_names'                      => $columnNames,
            'afterhours_all'                    => $afterhoursAll,
            'afterhours_spent'                  => $afterhoursSpent,
            'afterhours_made'                   => $afterhoursMade,
            'seconds_all'                       => $secondsAll,
            'days_all'                          => $daysAll,
            'remaining_time_to_spend_per_goal'  => $remainingTimeToSpendPerGoal,
            'ajax_render'                       => $ajaxRender,
            'skip_rewriting_twig_vars_to_js'    => $skipRewritingTwigVarsToJs,
            'page_title'                        => $this->getJobAfterhoursPageTitle(),
        ];

        return $this->render('modules/my-job/afterhours.html.twig', $twigData);
    }


    /**
     * @Route("/my-job/afterhours/update/",name="my-job-afterhours-update")
     * @param Request $request
     * @return JsonResponse
     * @throws MappingException
     */
    public function update(Request $request): JsonResponse
    {
        $parameters = $request->request->all();
        $entityId  = trim($parameters['id']);

        $entity   = $this->controllers->getMyJobAfterhoursController()->findOneById($entityId);
        $response = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
    }

    /**
     * @Route("/my-job/afterhours/remove/",name="my-job-afterhours-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response
    {

        $response = $this->app->repositories->deleteById(
            Repositories::MY_JOB_AFTERHOURS_REPOSITORY_NAME,
            $request->request->get('id')
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
     * @return FormInterface
     */
    private function getForm(): FormInterface
    {
        $goalsWithTimes = $this->controllers->getMyJobAfterhoursController()->getGoalsWithTime();
        $goals          = [];

        foreach ($goalsWithTimes as $goalWithTime) {
            $goals[] = $goalWithTime[MyJobAfterhoursRepository::GOAL_FIELD];
        }

        return $this->createForm(MyJobAfterhoursType::class, null, [
            'entity_enums' => $this->entityEnums,
            'goals'        => $goals,
        ]);
    }

    /**
     * @param Request $request
     * @return void
     */
    private function addFormDataToDB(Request $request): void
    {

        $form = $this->getForm();
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
    private function getJobAfterhoursPageTitle(): string
    {
        return $this->app->translator->translate('job.afterhours.title');
    }

}
