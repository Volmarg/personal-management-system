<?php

namespace App\Controller\Modules\Achievements;

use App\Controller\Utils\AjaxResponse;
use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Entity\Modules\Achievements\Achievement;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class AchievementController extends AbstractController {

    /**
     * @var Application
     */
    private $app;

    private $enum_types = [];

    public function __construct(Application $app) {
        $enum_type        = [Achievement::ENUM_SIMPLE, Achievement::ENUM_MEDIUM, Achievement::ENUM_HARD, Achievement::ENUM_HARDCORE];
        $this->enum_types = array_combine(
            array_map('ucfirst', array_values($enum_type)),
            $enum_type
        );

        $this->app = $app;
    }

    /**
     * @Route("/achievement", name="achievement")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {
        $this->addFormDataToDB($request);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderTemplate(false);
        }

        $template_content  = $this->renderTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     */
    protected function renderTemplate($ajax_render = false) {
        $achievement_form      = $this->app->forms->achievementForm(['enum_types' => $this->enum_types]);
        $achievement_form_view = $achievement_form->createView();

        $columns_names    = $this->getDoctrine()->getManager()->getClassMetadata(Achievement::class)->getColumnNames();
        $all_achievements = $this->app->repositories->achievementRepository->findBy(['deleted' => 0]);

        return $this->render('modules/my-achievements/index.html.twig', [
            'ajax_render'       => $ajax_render,
            'achievement_form'  => $achievement_form_view,
            'columns_names'     => $columns_names,
            'all_achievements'  => $all_achievements,
            'achievement_types' => $this->enum_types
        ]);
    }

    /**
     * @param Request $request
     */
    protected function addFormDataToDB(Request $request) {
        /**
         * @var FormInterface $achievement_form
         */
        $achievement_form = $this->app->forms->achievementForm(['enum_types' => $this->enum_types]);
        $achievement_form->handleRequest($request);

        if ($achievement_form->isSubmitted() && $achievement_form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($achievement_form->getData());
            $em->flush();
        }
    }

    /**
     * @Route("/achievement/update/",name="achievement-update")
     * @param Request $request
     * @return Response
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function update(Request $request) {

        $parameters = $request->request->all();
        $entity     = $this->app->repositories->achievementRepository->find($parameters['id']);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @Route("/achievement/remove/", name="achievement-remove")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function remove(Request $request): Response {
        /**
         * @var Achievement $achievement
         */
        $response = $this->app->repositories->deleteById(
            Repositories::ACHIEVEMENT_REPOSITORY_NAME,
            $request->request->get('id')
        );

        $message = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderTemplate(true);

            $template_content  = $rendered_template->getContent();
            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }

        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

}
