<?php


namespace App\Action\Core;


use App\Controller\Core\AjaxResponse;
use App\Controller\Core\Application;
use App\Controller\Core\Repositories;
use App\Services\Core\Translator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * @Route("/api/repository/remove/entity/{repositoryName}/{id}", name="api_repository_remove_entity")
     * @param string $repositoryName
     * @param $id
     * @param array $findByParams
     * @param Request|null $request
     * @return JsonResponse
     * @throws Exception
     */
    public function deleteById(string $repositoryName, $id, array $findByParams = [], ?Request $request = null ): JsonResponse
    {
        $response = $this->app->repositories->deleteById($repositoryName, $id, $findByParams, $request);
        return $response;
    }

    /**
     * @Route("/api/repository/update/entity", name="api_repository_update_entity")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function updateByRequest(Request $request): JsonResponse
    {
        $ajaxResponse = new AjaxResponse();

        if( !$request->request->has(self::KEY_PARAMETERS) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_PARAMETERS;

            $ajaxResponse->setMessage($message);
            $ajaxResponse->setSuccess(false);
            $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
            return $ajaxResponse->buildJsonResponse();
        }

        if( !$request->request->has(self::KEY_ENTITY_ID) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_ENTITY_ID;

            $ajaxResponse->setMessage($message);
            $ajaxResponse->setSuccess(false);
            $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
            return $ajaxResponse->buildJsonResponse();
        }

        if( !$request->request->has(self::KEY_REPOSITORY_NAME) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_REPOSITORY_NAME;

            $ajaxResponse->setMessage($message);
            $ajaxResponse->setSuccess(false);
            $ajaxResponse->setCode(Response::HTTP_BAD_REQUEST);
            return $ajaxResponse->buildJsonResponse();
        }

        $parameters      = $request->request->get(self::KEY_PARAMETERS);
        $id              = $request->request->get(self::KEY_ENTITY_ID);
        $repositoryName = $request->request->get(self::KEY_REPOSITORY_NAME);

        try{
            $id         = $this->app->repositories->trimAndCheckId($id);
            $repository = $this->{lcfirst($repositoryName)};
            $entity     = $repository->find($id);

            $response     = $this->app->repositories->update($parameters, $entity);
            $jsonResponse = AjaxResponse::initializeFromResponse($response)->buildJsonResponse();
        }catch(Exception $e){
            $this->app->logExceptionWasThrown($e);

            $message = $this->translator->translate('messages.general.internalServerError');

            $ajaxResponse->setMessage($message);
            $ajaxResponse->setSuccess(false);
            $ajaxResponse->setCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $ajaxResponse->buildJsonResponse();
        }

        return $jsonResponse;
    }

    /**
     * This function is used to toggle value of bool column in DB
     *  can be used for example with data attr for actions
     * @Route("/api/repository/toggle-boolval/{entityId}/{repositoryName}/{fieldName}", name="api_repository_toggle_boolval", methods="GET")
     * @param string $entityId
     * @param string $repositoryName
     * @param string $fieldName
     * @param Request $request
     * @return JsonResponse
     *
     * @throws MappingException
     * @throws Exception
     */
    public function toggleBool(string $entityId, string $repositoryName, string $fieldName, Request $request)
    {
        if( empty($entityId) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_ENTITY_ID;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        if( empty($repositoryName) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_REPOSITORY_NAME;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        if( empty($fieldName) ){
            $message = $this->translator->translate('missingRequiredParameter') . self::KEY_FIELD_NAME;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        $normalizedRepositoryNameForProperty = lcfirst($repositoryName);

        if( !property_exists($this->app->repositories, $normalizedRepositoryNameForProperty) ){
            $message = $this->translator->translate('messages.general.noSuchRepositoryWasFound') . $repositoryName;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        /**
         * @var ServiceEntityRepository $repository
         */
        $id         = $this->app->repositories->trimAndCheckId($entityId);
        $repository = $this->app->repositories->$normalizedRepositoryNameForProperty;
        $entity     = $repository->find($id);

        if( empty($entity) ){
            $message = $this->translator->translate('messages.general.noEntityWasFoundForId') . $id;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        $recordClassName  = get_class($entity);
        $classMeta        = $this->app->em->getClassMetadata($recordClassName);

        $tableName        = $classMeta->getTableName();
        $fieldMapping     = $classMeta->getFieldMapping($fieldName);
        $fieldType        = $fieldMapping['type'];

        $columnName       = Application::camelCaseToSnakeCaseConverter($fieldName);

        $columnsNames     = $this->app->repositories->getColumnsNamesForTableName($tableName);
        $isRecordEntity   = Repositories::isEntity($entity);

        $className        = get_class($entity);

        if( !$isRecordEntity ){
            $message = $this->translator->translate('messages.general.givenClassIsNotEntity') . $className;
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        if( !in_array($columnName, $columnsNames) ){
            $message = $this->translator->translate('messages.general.noColumnWithThisNameWasFoundInGivenTable') . "{$columnName} ({$tableName})";
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        if( Repositories::DOCTRINE_FIELD_MAPPING_TYPE_BOOLEAN !== $fieldType ){
            $message = $this->translator->translate('messages.general.thisFieldIsNotBoolean') . "{$fieldName} ({$tableName})";
            return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        try{
            $normalizedFieldNameForMethod = ucfirst($fieldName);
            $getterMethodName             = "get{$normalizedFieldNameForMethod}";
            $isserMethodName              = "is{$normalizedFieldNameForMethod}";
            $setterMethodName             = "set{$normalizedFieldNameForMethod}";

            $usedMethod = null;

            if( method_exists($entity, $getterMethodName) ){
                $usedMethod = $getterMethodName;
            }elseif( method_exists($entity, $isserMethodName) ){
                $usedMethod = $isserMethodName;
            }else{
                $message = $this->translator->translate('messages.general.noSuchGetterAndIsserAvailableForClass') . "{$normalizedFieldNameForMethod} ({$className})";
                return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
            }

            $boolVal         = (bool)$entity->$usedMethod();
            $invertedBoolVal = !$boolVal;

            if( !method_exists($entity, $setterMethodName)){
                $message = $this->translator->translate('messages.general.noSuchMethodExistsForGivenClass') . "{$setterMethodName} ({$className})";
                return AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
            }

            $entity->$setterMethodName($invertedBoolVal);

            $this->app->em->persist($entity);
            $this->app->em->flush();

            $message  = $this->translator->translate("messages.general.boolValueHasBeenToggled");
            $response = AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_OK, $message);
        }catch(Exception $e){
            $this->app->logger->critical("Exception was thrown while updating entity via action", [
                "exceptionMessage" => $e->getMessage(),
                "exceptionCode"    => $e->getCode(),
            ]);

            $message  = $this->translator->translate('messages.general.internalServerError');
            $response = AjaxResponse::buildJsonResponseForAjaxCall(Response::HTTP_INTERNAL_SERVER_ERROR, $message);
        }

        return $response;
    }

}