<?php


namespace App\Action\Files;

use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Controllers;
use App\Controller\Core\Env;
use App\Controller\Files\FileUploadController;
use App\Controller\Modules\ModulesController;
use App\Controller\Utils\Utils;
use App\Form\Files\UploadSubdirectoryCopyDataType;
use App\Services\Files\DirectoriesHandler;
use App\Services\Files\FilesHandler;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FilesUploadSettingsAction extends AbstractController {

    const TWIG_TEMPLATE_FILE_UPLOAD_SETTINGS = 'modules/common/files-upload-settings.html.twig';

    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Controllers $controllers
     */
    private $controllers;

    /**
     * @var DirectoriesHandler $directories_handler
     */
    private $directories_handler;

    /**
     * @var FilesHandler $files_handler
     */
    private $files_handler;

    public function __construct(
        Controllers         $controllers,
        Application         $app,
        DirectoriesHandler  $directories_handler,
        FilesHandler        $files_handler
    ) {
        $this->app                 = $app;
        $this->controllers         = $controllers;
        $this->directories_handler = $directories_handler;
        $this->files_handler       = $files_handler;
    }

    /**
     * @Route("upload/settings", name="upload_settings")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function displaySettings(Request $request) {

        if (!$request->isXmlHttpRequest()) {
            return $this->renderSettingsPage(false, $request);
        }

        $template_content = $this->renderSettingsPage(true, $request)->getContent();
        return AjaxResponse::buildJsonResponseForAjaxCall(200, "", $template_content);
    }

    /**
     * @param bool $ajax_render
     * @return Response
     * @throws Exception
     */
    private function renderSettingsPage(bool $ajax_render){

        $rename_form        = $this->app->forms->renameSubdirectoryForm();
        $create_subdir_form = $this->app->forms->createSubdirectoryForm();

        $copy_data_form = $this->app->forms->copyUploadSubdirectoryDataForm();

        $menu_node_modules_names_to_reload = [
            ModulesController::MODULE_NAME_IMAGES,
            ModulesController::MODULE_NAME_FILES,
            ModulesController::MODULE_NAME_VIDEO,
        ];

        $data = [
            'ajax_render'                       => $ajax_render,
            'rename_form'                       => $rename_form->createView(),
            'copy_data_form'                    => $copy_data_form->createView(),
            'create_subdir_form'                => $create_subdir_form->createView(),
            "menu_node_modules_names_to_reload" => Utils::escapedDoubleQuoteJsonEncode($menu_node_modules_names_to_reload),
        ];

        return $this->render(static::TWIG_TEMPLATE_FILE_UPLOAD_SETTINGS, $data);
    }

}