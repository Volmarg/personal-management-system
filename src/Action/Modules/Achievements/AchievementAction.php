<?php

namespace App\Action\Modules\Achievements;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Repositories;
use App\Entity\Modules\Achievements\Achievement;
use App\Form\Modules\Achievements\AchievementType;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\System\ModuleAnnotation;

/**
 * Class AchievementAction
 * @package App\Action\Modules\Achievements
 * @ModuleAnnotation(
 *     name=App\Controller\Modules\ModulesController::MODULE_NAME_ACHIEVEMENTS
 * )
 */
class AchievementAction extends AbstractController {

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
     * @var array $enumTypes
     */
    private array $enumTypes;

    /**
     * AchievementAction constructor.
     * @param Application $app
     * @param Controllers $controllers
     */
    public function __construct(Application $app, Controllers $controllers) {
        $enumType        = [Achievement::ENUM_SIMPLE, Achievement::ENUM_MEDIUM, Achievement::ENUM_HARD, Achievement::ENUM_HARDCORE];
        $this->enumTypes = array_combine(
            array_map('ucfirst', array_values($enumType)),
            $enumType
        );

        $this->app         = $app;
        $this->controllers = $controllers;
    }

    /**
     * @Route("/achievement", name="achievement")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function display(Request $request): Response
    {
        $this->addFormDataToDB($request, $this->enumTypes);
        if (!$request->isXmlHttpRequest()) {
            return $this->renderAchievementPageTemplate();
        }

        $templateContent = $this->renderAchievementPageTemplate(true)->getContent();

        $ajaxResponse = new AjaxResponse("", $templateContent);
        $ajaxResponse->setPageTitle($this->getAchievementsPageTitle());
        $ajaxResponse->setCode(Response::HTTP_OK);

        return $ajaxResponse->buildJsonResponse();
    }

    /**
     * @Route("/achievement/update/",name="achievement-update")
     * @param Request $request
     * @return Response
     *
     * #todo: check if this works correct in case of edit and update
     * @throws MappingException
     */
    public function update(Request $request): Response
    {
        $parameters = $request->request->all();
        $id         = trim($parameters[self::PARAMETER_ID]);

        $entity     = $this->controllers->getAchievementController()->getOneById($id);
        $response   = $this->app->repositories->update($parameters, $entity);

        return AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
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
            $renderedTemplate = $this->renderAchievementPageTemplate(true, true);
            $templateContent  = $renderedTemplate->getContent();
            return AjaxResponse::buildJsonResponseForAjaxCall(200, $message, $templateContent);
        }

        return AjaxResponse::buildJsonResponseForAjaxCall(500, $message);
    }

    /**
     * @param bool $ajaxRender
     * @param bool $skipRewritingTwigVarsToJs
     * @return Response
     */
    private function renderAchievementPageTemplate(bool $ajaxRender = false, bool $skipRewritingTwigVarsToJs = false): Response
    {
        $achievementForm     = $this->app->forms->achievementForm([AchievementType::KEY_OPTION_ENUM_TYPES => $this->enumTypes]);
        $achievementFormView = $achievementForm->createView();

        $columnsNames    = $this->getDoctrine()->getManager()->getClassMetadata(Achievement::class)->getColumnNames();
        $allAchievements = $this->controllers->getAchievementController()->getAllNotDeleted();

        return $this->render('modules/my-achievements/index.html.twig', [
            'ajax_render'       => $ajaxRender,
            'achievement_form'  => $achievementFormView,
            'columns_names'     => $columnsNames,
            'all_achievements'  => $allAchievements,
            'achievement_types' => $this->enumTypes,
            'page_title'        => $this->getAchievementsPageTitle(),
            'skip_rewriting_twig_vars_to_js' => $skipRewritingTwigVarsToJs,
        ]);
    }

    /**
     * @param Request $request
     * @param array $enumTypes
     */
    private function addFormDataToDB(Request $request, array $enumTypes)
    {
        $achievementForm = $this->app->forms->achievementForm([AchievementType::KEY_OPTION_ENUM_TYPES => $enumTypes]);
        $achievementForm->handleRequest($request);

        if ($achievementForm->isSubmitted() && $achievementForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($achievementForm->getData());
            $em->flush();
        }
    }

    /**
     * Will return achievements page title
     *
     * @return string
     */
    private function getAchievementsPageTitle(): string
    {
        return $this->app->translator->translate('achievements.title');
    }

}