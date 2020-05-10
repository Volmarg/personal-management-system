<?php


namespace App\Action\Core;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Repositories;
use App\Services\Core\Translator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\MappingException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RepositoriesAction extends AbstractController {


    const KEY_PARAMETERS        = 'parameters';
    const KEY_ENTITY_ID         = 'entity_id';
    const KEY_FIELD_NAME        = 'field_name';
    const KEY_REPOSITORY_NAME   = 'repository_name';


    /**
     * @var Application $app
     */
    private $app;

    /**
     * @var Translator $translator
     */
    private $translator;

    public function __construct(Application $app, Translator $translator) {
        $this->app        = $app;
        $this->translator = $translator;
    }

    /**
     * @Route("/api/repository/remove/entity/{repository_name}/{id}", name="api_repository_remove_entity")
     * @param string $repository_name
     * @param $id
     * @param array $findByParams
     * @param Request|null $request
     * @return Response
     *
     */
    public function deleteById(string $repository_name, $id, array $findByParams = [], ?Request $request = null ): Response
    {
        $response = $this->app->repositories->deleteById($repository_name, $id, $findByParams, $request);
        return $response;
    }


    /**
     * @Route("/api/repository/update/entity", name="api_repository_update_entity")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function updateByRequest(Request $request){

        if( !$request->request->has(self::KEY_PARAMETERS) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_PARAMETERS;
            return new JsonResponse($message, 500);
        }

        if( !$request->request->has(self::KEY_ENTITY_ID) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_ENTITY_ID;
            return new JsonResponse($message, 500);
        }

        if( !$request->request->has(self::KEY_REPOSITORY_NAME) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_REPOSITORY_NAME;
            return new JsonResponse($message, 500);
        }

        $parameters      = $request->request->get(self::KEY_PARAMETERS);
        $id              = $request->request->get(self::KEY_ENTITY_ID);
        $repository_name = $request->request->get(self::KEY_REPOSITORY_NAME);

        try{
            $id         = $this->app->repositories->trimAndCheckId($id);
            $repository = $this->{lcfirst($repository_name)};
            $entity     = $repository->find($id);

            $response = $this->app->repositories->update($parameters, $entity);
        }catch(Exception $e){
            $this->app->logger->critical("Exception was thrown while updating entity via action", [
                "exceptionMessage" => $e->getMessage(),
                "exceptionCode"    => $e->getCode(),
            ]);

            $message  = $this->translator->translate('messages.general.internalServerError');
            $response = new JsonResponse($message, 500);
        }

        return $response;
    }

    /**
     * This function is used to toggle value of bool column in DB
     *  can be used for example with data attr for actions
     * @Route("/api/repository/toggle-boolval/{entity_id}/{repository_name}/{field_name}", name="api_repository_toggle_boolval", methods="GET")
     * @param string $entity_id
     * @param string $repository_name
     * @param string $field_name
     * @param Request $request
     * @return JsonResponse
     *
     * @throws MappingException
     * @throws Exception
     */
    public function toggleBool(string $entity_id, string $repository_name, string $field_name, Request $request)
    {
        if( empty($entity_id) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_ENTITY_ID;
            return AjaxResponse::buildResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        if( empty($repository_name) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_REPOSITORY_NAME;
            return AjaxResponse::buildResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        if( empty($field_name) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_FIELD_NAME;
            return AjaxResponse::buildResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        $normalizedRepositoryNameForProperty = lcfirst($repository_name);

        if( !property_exists($this->app->repositories, $normalizedRepositoryNameForProperty) ){
            $message = $this->translator->translate('messages.general.noSuchRepositoryWasFound') . $repository_name;
            return AjaxResponse::buildResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        /**
         * @var ServiceEntityRepository $repository
         */
        $id         = $this->app->repositories->trimAndCheckId($entity_id);
        $repository = $this->app->repositories->$normalizedRepositoryNameForProperty;
        $entity     = $repository->find($id);

        if( empty($entity) ){
            $message = $this->translator->translate('messages.general.noEntityWasFoundForId') . $id;
            return AjaxResponse::buildResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        $record_class_name  = get_class($entity);
        $class_meta         = $this->app->em->getClassMetadata($record_class_name);

        $table_name         = $class_meta->getTableName();
        $field_mapping      = $class_meta->getFieldMapping($field_name);
        $field_type         = $field_mapping['type'];

        $column_name        = $this->app->camelCaseToSnakeCaseConverter($field_name);

        $columns_names      = $this->app->repositories->getColumnsNamesForTableName($table_name);
        $is_record_entity   = $this->app->repositories->isEntityClass($record_class_name);

        $class_name         = get_class($entity);

        if( !$is_record_entity ){
            $message = $this->translator->translate('messages.general.givenClassIsNotEntity') . $class_name;
            return AjaxResponse::buildResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        if( !in_array($column_name, $columns_names) ){
            $message = $this->translator->translate('messages.general.noColumnWithThisNameWasFoundInGivenTable') . "{$column_name} ({$table_name})";
            return AjaxResponse::buildResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        if( Repositories::DOCTRINE_FIELD_MAPPING_TYPE_BOOLEAN !== $field_type ){
            $message = $this->translator->translate('messages.general.thisFieldIsNotBoolean') . "{$field_name} ({$table_name})";
            return AjaxResponse::buildResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        try{
            $normalizedFieldNameForMethod = ucfirst($field_name);
            $getterMethodName             = "get{$normalizedFieldNameForMethod}";
            $isserMethodName              = "is{$normalizedFieldNameForMethod}";
            $setterMethodName             = "set{$normalizedFieldNameForMethod}";

            $usedMethod = null;

            if( method_exists($entity, $getterMethodName) ){
                $usedMethod = $getterMethodName;
            }elseif( method_exists($entity, $isserMethodName) ){
                $usedMethod = $isserMethodName;
            }else{
                $message = $this->translator->translate('messages.general.noSuchGetterAndIsserAvailableForClass') . "{$normalizedFieldNameForMethod} ({$class_name})";
                return AjaxResponse::buildResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
            }

            $boolVal         = (bool)$entity->$usedMethod();
            $invertedBoolVal = !$boolVal;

            if( !method_exists($entity, $setterMethodName)){
                $message = $this->translator->translate('messages.general.noSuchMethodExistsForGivenClass') . "{$setterMethodName} ({$class_name})";
                return AjaxResponse::buildResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
            }

            $entity->$setterMethodName($invertedBoolVal);

            $this->app->em->persist($entity);
            $this->app->em->flush();

            $message  = $this->translator->translate("messages.general.boolValueHasBeenToggled");
            $response = AjaxResponse::buildResponseForAjaxCall(Response::HTTP_OK, $message);
        }catch(Exception $e){
            $this->app->logger->critical("Exception was thrown while updating entity via action", [
                "exceptionMessage" => $e->getMessage(),
                "exceptionCode"    => $e->getCode(),
            ]);

            $message  = $this->translator->translate('messages.general.internalServerError');
            $response = AjaxResponse::buildResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        return $response;
    }

}