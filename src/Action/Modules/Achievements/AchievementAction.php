<?php

namespace App\Action\Modules\Achievements;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Achievements\Achievement;
use App\Form\Modules\Achievements\AchievementType;
use App\Repository\AbstractRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AchievementAction extends AbstractController {

    const PARAMETER_ID = "id";

    /**
     * @var Application $app
     */
    private $app = null;

    /**
     * @var Controllers $controllers
     */
    private $controllers = null;

    /**
     * @var array $enum_types
     */
    private $enum_types = [];

    public function __construct(Application $app, Controllers $controllers) {
        $enum_type        = [Achievement::ENUM_SIMPLE, Achievement::ENUM_MEDIUM, Achievement::ENUM_HARD, Achievement::ENUM_HARDCORE];
        $this->enum_types = array_combine(
            array_map('ucfirst', array_values($enum_type)),
            $enum_type
        );

        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/achievement", name="achievement")
     * @param Request $request
     * @return Response
     */
    public function display(Request $request) {
        $this->addFormDataToDB($request, $this->enum_types);

        if (!$request->isXmlHttpRequest()) {
            return $this->renderAchievementPageTemplate(false);
        }

        $template_content  = $this->renderAchievementPageTemplate(true)->getContent();
        return AjaxResponse::buildResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @Route("/achievement/update/",name="achievement-update")
     * @param Request $request
     * @return Response
     *
     * #todo: check if this works correct in case of edit and update
     */
    public function update(Request $request) {

        $parameters = $request->request->all();
        $id         = $parameters[self::PARAMETER_ID];

        $entity     = $this->app->repositories->achievementRepository->find($id);
        $response   = $this->app->repositories->update($parameters, $entity);

        return $response;
    }

    /**
     * @Route("/achievement/remove/", name="achievement-remove")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function remove(Request $request): Response {

        $id = $request->request->get(self::PARAMETER_ID);

        $response = $this->app->repositories->deleteById(Repositories::ACHIEVEMENT_REPOSITORY_NAME, $id);
        $message  = $response->getContent();

        if ($response->getStatusCode() == 200) {
            $rendered_template = $this->renderAchievementPageTemplate(true, true);

            $template_content  = $rendered_template->getContent();
            return AjaxResponse::buildResponseForAjaxCall(200, $message, $template_content);
        }

        return AjaxResponse::buildResponseForAjaxCall(500, $message);
    }

    /**
     * @param bool $ajax_render
     * @param bool $skip_rewriting_twig_vars_to_js
     * @return Response
     */
    private function renderAchievementPageTemplate(bool $ajax_render = false, bool $skip_rewriting_twig_vars_to_js = false) {
        $achievement_form      = $this->app->forms->achievementForm([AchievementType::KEY_OPTION_ENUM_TYPES => $this->enum_types]);
        $achievement_form_view = $achievement_form->createView();

        $columns_names    = $this->getDoctrine()->getManager()->getClassMetadata(Achievement::class)->getColumnNames();
        $all_achievements = $this->app->repositories->achievementRepository->findBy([AbstractRepository::FIELD_DELETED => 0]);

        return $this->render('modules/my-achievements/index.html.twig', [
            'ajax_render'       => $ajax_render,
            'achievement_form'  => $achievement_form_view,
            'columns_names'     => $columns_names,
            'all_achievements'  => $all_achievements,
            'achievement_types' => $this->enum_types,
            'skip_rewriting_twig_vars_to_js' => $skip_rewriting_twig_vars_to_js,
        ]);
    }

    /**
     * @param Request $request
     * @param array $enum_types
     */
    private function addFormDataToDB(Request $request, array $enum_types) {
        /**
         * @var FormInterface $achievement_form
         */
        $achievement_form = $this->app->forms->achievementForm([AchievementType::KEY_OPTION_ENUM_TYPES => $enum_types]);
        $achievement_form->handleRequest($request);

        if ($achievement_form->isSubmitted() && $achievement_form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($achievement_form->getData());
            $em->flush();
        }
    }

}