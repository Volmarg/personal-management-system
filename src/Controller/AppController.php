<?php

namespace App\Controller;

use App\Controller\Modules\ModulesController;
use App\Controller\Utils\Application;
use App\Controller\Utils\Repositories;
use App\Entity\Modules\Goals\MyGoals;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AppController extends Controller
{
    const TWIG_MENU_NODE_PATH = 'page-elements/components/sidebar/menu-nodes/';
    const TWIG_EXT            = DOT.'twig';

    const KEY_MENU_NODE_MODULE_NAME = 'menu_node_module_name';

    const KEY_MESSAGE   = 'message';
    const KEY_CODE      = 'code';
    const KEY_TPL       = 'tpl';
    const KEY_CURR_URL  = 'currUrl';

    const MENU_NODE_MODULE_NAME_ACHIEVEMENTS  = ModulesController::MODULE_NAME_ACHIEVEMENTS;
    const MENU_NODE_MODULE_NAME_GOALS         = ModulesController::MODULE_NAME_GOALS;
    const MENU_NODE_MODULE_NAME_MY_SCHEDULES  = ModulesController::MENU_NODE_MODULE_NAME_MY_SCHEDULES;
    const MENU_NODE_MODULE_NAME_MY_CONTACTS   = ModulesController::MODULE_NAME_CONTACTS;
    const MENU_NODE_MODULE_NAME_MY_FILES      = ModulesController::MODULE_NAME_FILES;
    const MENU_NODE_MODULE_NAME_MY_IMAGES     = ModulesController::MODULE_NAME_IMAGES;
    const MENU_NODE_MODULE_NAME_MY_JOB        = ModulesController::MODULE_NAME_JOB;
    const MENU_NODE_MODULE_NAME_MY_PASSWORDS  = ModulesController::MODULE_NAME_PASSWORDS;
    const MENU_NODE_MODULE_NAME_MY_PAYMENTS   = ModulesController::MODULE_NAME_PAYMENTS;
    const MENU_NODE_MODULE_NAME_MY_SHOPPING   = ModulesController::MODULE_NAME_SHOPPING;
    const MENU_NODE_MODULE_NAME_MY_TRAVELS    = ModulesController::MODULE_NAME_TRAVELS;
    const MENU_NODE_MODULE_NAME_NOTES         = ModulesController::MODULE_NAME_NOTES;
    const MENU_NODE_MODULE_NAME_REPORTS       = ModulesController::MENU_NODE_MODULE_NAME_REPORTS;

    /**
     * For loading twig templates
     */
    const MENU_NODE_NAME_ACHIEVEMENTS = 'achievements';
    const MENU_NODE_NAME_GOALS        = 'goals';
    const MENU_NODE_NAME_MY_SCHEDULES = 'my-schedules';
    const MENU_NODE_NAME_MY_CONTACTS  = 'my-contacts';
    const MENU_NODE_NAME_MY_FILES     = 'my-files';
    const MENU_NODE_NAME_MY_IMAGES    = 'my-images';
    const MENU_NODE_NAME_MY_JOB       = 'my-job';
    const MENU_NODE_NAME_MY_PASSWORDS = 'my-passwords';
    const MENU_NODE_NAME_MY_PAYMENTS  = 'my-payments';
    const MENU_NODE_NAME_MY_SHOPPING  = 'my-shopping';
    const MENU_NODE_NAME_MY_TRAVELS   = 'my-travels';
    const MENU_NODE_NAME_NOTES        = 'notes';
    const MENU_NODE_NAME_REPORTS      = 'my-reports';

    const MENU_NODE_MODULES_NAMES_INTO_CONST_NAMES = [
        self::MENU_NODE_MODULE_NAME_ACHIEVEMENTS  => 'MENU_NODE_MODULE_NAME_ACHIEVEMENTS',
        self::MENU_NODE_MODULE_NAME_GOALS         => 'MENU_NODE_MODULE_NAME_GOALS',
        self::MENU_NODE_MODULE_NAME_MY_SCHEDULES  => 'MENU_NODE_MODULE_NAME_MY_SCHEDULES',
        self::MENU_NODE_MODULE_NAME_MY_CONTACTS   => 'MENU_NODE_MODULE_NAME_MY_CONTACTS',
        self::MENU_NODE_MODULE_NAME_MY_FILES      => 'MENU_NODE_MODULE_NAME_MY_FILES',
        self::MENU_NODE_MODULE_NAME_MY_IMAGES     => 'MENU_NODE_MODULE_NAME_MY_IMAGES',
        self::MENU_NODE_MODULE_NAME_MY_JOB        => 'MENU_NODE_MODULE_NAME_MY_JOB',
        self::MENU_NODE_MODULE_NAME_MY_PASSWORDS  => 'MENU_NODE_MODULE_NAME_MY_PASSWORDS',
        self::MENU_NODE_MODULE_NAME_MY_PAYMENTS   => 'MENU_NODE_MODULE_NAME_MY_PAYMENTS',
        self::MENU_NODE_MODULE_NAME_MY_SHOPPING   => 'MENU_NODE_MODULE_NAME_MY_SHOPPING',
        self::MENU_NODE_MODULE_NAME_MY_TRAVELS    => 'MENU_NODE_MODULE_NAME_MY_TRAVELS',
        self::MENU_NODE_MODULE_NAME_NOTES         => 'MENU_NODE_MODULE_NAME_NOTES',
        self::MENU_NODE_MODULE_NAME_REPORTS       => 'MENU_NODE_MODULE_NAME_REPORTS',
    ];

    const MENU_NODES_MODULES_NAMES_TO_TEMPLATES_MAP = [
        self::MENU_NODE_MODULE_NAME_ACHIEVEMENTS  => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_ACHIEVEMENTS . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_GOALS         => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_GOALS        . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_SCHEDULES  => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_SCHEDULES . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_CONTACTS   => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_CONTACTS  . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_FILES      => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_FILES     . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_IMAGES     => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_IMAGES    . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_JOB        => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_JOB       . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_PASSWORDS  => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_PASSWORDS . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_PAYMENTS   => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_PAYMENTS  . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_SHOPPING   => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_SHOPPING  . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_MY_TRAVELS    => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_MY_TRAVELS   . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_NOTES         => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_NOTES        . self::TWIG_EXT,
        self::MENU_NODE_MODULE_NAME_REPORTS       => self::TWIG_MENU_NODE_PATH . self::MENU_NODE_NAME_REPORTS      . self::TWIG_EXT,
    ];

    /**
     * @var Application $app
     */
    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    /**
     * @Route("/", name="app_default")
     * This is also main redirect when user logs in
     */
    public function index()
    {
        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/actions/render-menu-node-template", name="render_menu_node_template")
     * @param Request $request
     * @return Response
     */
    public function renderMenuNodeTemplate(Request $request) {

        $message = $this->app->translator->translate('responses.menu.nodeHasBeenRendered');
        $code    = 200;
        $tpl     = '';

        if ( !$request->request->has(static::KEY_MENU_NODE_MODULE_NAME) ) {
            $message = $this->app->translator->translate('responses.general.missingRequiredParameter') . static::KEY_MENU_NODE_MODULE_NAME;
            $code    = 500;

            $response_data = [
                static::KEY_MESSAGE => $message,
                static::KEY_CODE    => $code,
                static::KEY_TPL     => $tpl
            ];

            return new JsonResponse($response_data);
        }

        $menu_node_module_name = $request->request->get(static::KEY_MENU_NODE_MODULE_NAME);

        if ( !array_key_exists($menu_node_module_name, static::MENU_NODES_MODULES_NAMES_TO_TEMPLATES_MAP) ) {
            $message = $this->app->translator->translate('responses.menu.menuNodeWithNameWasNotFound') . $menu_node_module_name;
            $code    = 500;

            $response_data = [
                static::KEY_MESSAGE => $message,
                static::KEY_CODE    => $code,
                static::KEY_TPL     => $tpl
            ];

            return new JsonResponse($response_data);
        }

        $tpl_data = [
            static::KEY_CURR_URL => $request->server->get('HTTP_REFERER'),
            static::MENU_NODE_MODULES_NAMES_INTO_CONST_NAMES[$menu_node_module_name] => $menu_node_module_name // because of constants used in tpl
        ];

        $tpl = $this->render(static::MENU_NODES_MODULES_NAMES_TO_TEMPLATES_MAP[$menu_node_module_name], $tpl_data)->getContent();

        $response_data = [
            static::KEY_MESSAGE  => $message,
            static::KEY_CODE     => $code,
            static::KEY_TPL      => $tpl
        ];

        return new JsonResponse($response_data);
    }

    /**
     * This originally came with symfonator
     * @Route("admin/{pageName}", name="admin_default")
     * @param string $pageName Page name
     * @return Response
     */
    public function admin(string $pageName)
    {
        return $this->render(
            sprintf(
                "%s.html.twig",
                $pageName
            )
        );
    }

    /**
     * This originally came with symfonator
     * @Route("test", name="test")
     * @return Response
     */
    public function hasNotRemovedSoftDeletedRelatedEntities(EntityManagerInterface $em)
    {

        $record_id = 123;
        $record    = $this->app->repositories->myGoalsRepository->find($record_id);


        $class_name = get_class($record);

        $class_meta = $em->getClassMetadata($class_name);
        $related_entities_classes_data_arrays = $class_meta->getAssociationMappings();
        $are_all_related_entities_removed     = true;

        foreach( $related_entities_classes_data_arrays as $related_entity_class_data_array ){

            $field_name    = $related_entity_class_data_array[Repositories::KEY_CLASS_META_RELATED_ENTITY_FIELD_NAME];
            $mapped_by     = $related_entity_class_data_array[Repositories::KEY_CLASS_META_RELATED_ENTITY_MAPPED_BY];
            $target_entity = $related_entity_class_data_array[Repositories::KEY_CLASS_META_RELATED_ENTITY_TARGET_ENTITY];

            dump($field_name);
            dump($mapped_by);
            dump($target_entity);

            $get_method                              = "get" . ucfirst($field_name);
            $related_entities_persistent_collections = $record->{$get_method}();
            $related_entities                        = $related_entities_persistent_collections->getValues();

            foreach( $related_entities as $related_entity ){
                $delete_method_name = "getDeleted";

                if( property_exists($related_entity, $delete_method_name) ){
                    $is_deleted = $related_entity->$delete_method_name();

                    if( !$is_deleted ){
                        $are_all_related_entities_removed = false;
                    }
                }
            }
        }

        dump($are_all_related_entities_removed);

        die();
    }

}
